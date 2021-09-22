<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Bhfs\OrdersApi\Observer\Checkout;

use Amasty\Storelocator\Model\AttributeFactory;
use Amasty\Storelocator\Model\LocationFactory;
use Amasty\StorePickupWithLocator\Api\Data\QuoteInterface;
use Amasty\StorePickupWithLocator\Model\Carrier\Shipping;
use Amasty\StorePickupWithLocator\Model\QuoteRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Webapi\Soap\ClientFactory;

class SubmitAllAfter implements ObserverInterface
{

    /**
     * Execute observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */

    // 
    private $authenticationToken;
    // test vars in live mode replace with online store (default)
    private $clf_username = "besthealthfoodshopworthing";
    private $clf_password = "XFRy8atUvK";
    private $ws_endpoint = "http://services.clfdistribution.com:8080/CLFWebOrdering_Test/WebOrdering.asmx?WSDL";

    private $searchCriteriaBuilder;

    private $quoteRepository;

    private $locationFactory;

    private $attributeFactory;

    protected $soapClientFactory;


    public function __construct(
        LocationFactory $locationFactory,
        QuoteRepository $quoteRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        AttributeFactory $attributeFactory,
        ClientFactory $soapClientFactory
    )
    {
        $this->locationFactory = $locationFactory;
        $this->quoteRepository = $quoteRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->attributeFactory = $attributeFactory;
        $this->soapClientFactory = $soapClientFactory;
    }

    public function execute(
        Observer $observer
    ) {

        if (!$order = $observer->getEvent()->getOrder()) {
            return $this;
        }
        // observer code
        $order_id = $order->getEntityId();
        $order_increment_id = $order->getIncrementId();
        $customerId = $order->getCustomerId();
        $order_state = $order->getState();
        $order_status = $order->getStatus();

        // fetch specific payment information
        $amount = $order->getPayment()->getAmountPaid();
        $paymentMethod = $order->getPayment()->getMethod();
        $info = $order->getPayment()->getAdditionalInformation('method_title');

        // get shipping info
        // this will determin if local pickup or online order
        $shipping_method = $order->getShippingMethod(); 
        $shipping_description = $order->getShippingDescription();

        if ( $order->getShippingMethod() == Shipping::SHIPPING_NAME ) {
            
            $this->searchCriteriaBuilder->addFilter(QuoteInterface::QUOTE_ID, $order->getQuoteId());
            $searchCriteria = $this->searchCriteriaBuilder->create();
            $quotes = $this->quoteRepository->getList($searchCriteria)->getItems();

            if (!count($quotes)) {
                return $this;
            }

            $quote = array_shift($quotes);
            $locationId = $quote->getStoreId();
            $locationFactory = $this->locationFactory->create();
            $location = $locationFactory->getCollection()
            ->addFieldToFilter('id', $locationId)
            ->getFirstItem();
            $location->getResource()->setAttributesData($location);
            $attributes = $location->getAttributes();

        } else {

            // get "online" store information
            $locationId = 5;
            $locationFactory = $this->locationFactory->create();
            $location = $locationFactory->getCollection()
            ->addFieldToFilter('id', $locationId)
            ->getFirstItem();
            $location->getResource()->setAttributesData($location);
            $attributes = $location->getAttributes();

        }

        $clf_username = $attributes['clf_username']['option_title'];
        $clf_password = $attributes['clf_password']['option_title'];
        $ws_endpoint = $attributes['ws_endpoint']['option_title'];


        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/bhfs.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info('*************************');
        $logger->info('Catched event succssfully');
        $logger->info('Order ID# ' . $order_id);
        $logger->info('Order state: ' . $order_state);
        $logger->info('Order status: ' . $order_status);
        $logger->info('Order payment method: ' . $paymentMethod);
        $logger->info('Order info: ' . $info);
        $logger->info('Order shipping method: ' . $shipping_method);
        $logger->info('Order shipping description: ' . $shipping_description);

        $logger->info('Clf username: ' . $clf_username);
        $logger->info('Clf password: ' . $clf_password);
        $logger->info('WS endpoint: ' . $ws_endpoint);

        $logger->info('location: ' . $location->getCity());
        $logger->info('location: ' . $location->getAddress());
        $logger->info('location attributes: ' . print_r($attributes, true));
        // $logger->info('location attributes: ' . print_r( $attributes ));
        
        // get authentication token
        // Specify url
         $soapClient = $this->soapClientFactory->create($ws_endpoint, array('trace' => 1));

        // Prepare SoapHeader parameters
         $sh_param = array('AuthenticationToken' => '', 'ErrorMessage' => '');
         $headers = new \SoapHeader('http://services.clfdistribution.com/CLFWebOrdering',
        'WebServiceHeader', $sh_param);
         // Prepare Soap Client
         $soapClient->__setSoapHeaders(array($headers));
         // Setup the GetAuthenticationToken parameters
         $ap_param = array(
             'Username' => $clf_username,
             'Password' => $clf_password);
         // Call GetAuthenticationToken
         $error = 0;
         try
         {
            $res = $soapClient->GetAuthenticationToken($ap_param);
         }
         catch (SoapFault $fault)
         {
             $error = 1;
             // echo 'GetAuthenticationToken returned the following error:<br />' . $fault->faultcode . '-' . $fault->faultstring;
             $logger->info('GetAuthenticationToken returned the following error:' . $fault->faultcode . '-' . $fault->faultstring);
             return $this;
         }
         if ($error == 0)
         {
            $authenticationToken = $res->GetAuthenticationTokenResult;
         }

        $logger->info('CLF authentication token: ' . $authenticationToken);

        $shippingcity = $location->getCity();
        $shippingstreet = $location->getAddress();
        $shippingpostcode = ""; //$location->getZip();      
        $shippingtelephone = $location->getPhone();

        $xml_header = '<order></order>';
        $xml = new \SimpleXMLElement($xml_header);

        // delivery
        $subnode_delivery = $xml->addChild('delivery');
        $subnode_delivery->addChild('name', $shipping_description);
        $subnode_delivery->addChild('company', $shippingstreet);
        $subnode_delivery->addChild('address1', $shippingstreet);
        $subnode_delivery->addChild('address2', '');
        $subnode_delivery->addChild('town', $shippingcity);
        $subnode_delivery->addChild('county', '');
        $subnode_delivery->addChild('postcode', $shippingpostcode);
        $subnode_delivery->addChild('country', 'GB');
        $subnode_delivery->addChild('phone', $shippingtelephone);
        $subnode_delivery->addChild('email', '');
        $subnode_shopper = $xml->addChild('shopper');
        // For deliveries to the UK the list can be retrieved using the GetUKShippingZones web method.
        $subnode_shopper->addChild('shippingzone', 'England');
        // The shipping method can be left blank, in this scenario the cheapest rate will be used.
        $subnode_shopper->addChild('shippingmethod', '');
        // This is additional information sent to CLF, e.g. Please send packing slip only        
        $subnode_shopper->addChild('comments', '*** Please send packing slip only ***&lt;br/&gt;*** DO NOT INCLUDE ANY PAPER WORK ***');
        // This element is optional and it’s an extra piece of information that’sdisplayed on the invoice received by the customer. However it is recommended that this value is specified and it has to be unique for each order.
        $subnode_shopper->addChild('yourreference', $order_increment_id);
        //  This element is optional and it’s an extra piece of information that’s displayed on the invoice received by the customer. However it is recommended that this value is specified and it has to be unique for each order.
        $subnode_shopper->addChild('interimorder', '1');
        // items
        $subnode_items = $xml->addChild('items');
        // Get Order Items
        $orderItems = $order->getAllItems();

        foreach ($orderItems as $item) {

            $subnode_item = $subnode_items->addChild('item');
            $subnode_item->addChild('sku', $item->getSku());
            $qtyordered = (float)$item->getQtyOrdered();
            $subnode_item->addChild('qtyordered', number_format($qtyordered, 0) );
            // <item> <sku>AA-HF460</sku> <qtyordered>12</qtyordered></item>

        }

        // $xml = htmlentities($xml->asXML());

        // $orderXml_string = "<orderXml>" . $xml . "</orderXml>";

        // Prepare SoapHeader parameters
        $sh_param = array('AuthenticationToken' => $authenticationToken, 'ErrorMessage' => '');
        $headers = new \SoapHeader('http://services.clfdistribution.com/CLFWebOrdering', 'WebServiceHeader', $sh_param);
        // Prepare Soap Client
        $soapClient->__setSoapHeaders(array($headers));

        // Setup the PlaceCLFOrder parameters
        $ap_param = array(
             'orderXml' => $xml->asXML());
        // 
        $error = 0;
        try
        {
            $res = $soapClient->PlaceCLFOrder($ap_param);
        }
        catch (SoapFault $fault)
        {
            $error = 1;
            $clf_order_result = $fault->faultcode;
        }
        if ($error == 0)
        {
            $clf_order_result = $res->PlaceCLFOrderResult;
        }

        if($clf_order_result == ""){
          $clf_order_result = htmlspecialchars($soapOrderClient->__getLastResponse());  
        }
        $logger->info('order xml: ' . $xml->asXML());

        // add order comment with CLF order id etc
        $order->addStatusToHistory($order->getStatus(),'CLF order created: ' . $clf_order_result ,false);
        $order->save();

        $logger->info('order comment updated');
        $logger->info('CLF order created, ref #' . $clf_order_result);

    }


    
}

