<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Bhfs\OrdersApi\Controller\Index;

use Amasty\Storelocator\Model\AttributeFactory;
use Amasty\Storelocator\Model\LocationFactory;
use Amasty\StorePickupWithLocator\Api\Data\QuoteInterface;
use Amasty\StorePickupWithLocator\Model\Carrier\Shipping;
use Amasty\StorePickupWithLocator\Model\QuoteRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Webapi\Soap\ClientFactory;

use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;

class Index extends \Magento\Framework\App\Action\Action
{

    protected $resultPageFactory;
    protected $orderRepository;
    protected $orderId = 38;

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


    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context  $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        OrderRepositoryInterface $orderRepository,
        LocationFactory $locationFactory,
        QuoteRepository $quoteRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        AttributeFactory $attributeFactory,
        ClientFactory $soapClientFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->orderRepository = $orderRepository;
        parent::__construct($context);
        $this->locationFactory = $locationFactory;
        $this->quoteRepository = $quoteRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->attributeFactory = $attributeFactory;
        $this->soapClientFactory = $soapClientFactory;
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $order = $this->orderRepository->get($this->orderId);
        $orderIncrementId = $order->getIncrementId(); // To get order incremental id

        // get shipping info
        // this will determin if local pickup or online order
        $shipping_method = $order->getShippingMethod(); 
        $shipping_description = $order->getShippingDescription();

        // get "online" store information
        $locationId = 5;
        $locationFactory = $this->locationFactory->create();
        $location = $locationFactory->getCollection()
        ->addFieldToFilter('id', $locationId)
        ->getFirstItem();
        $location->getResource()->setAttributesData($location);
        $attributes = $location->getAttributes();

        $location_name = $location->getName();
        print_r($attributes);

        $clf_username = $attributes['clf_username']['option_title'];
        $clf_password = $attributes['clf_password']['option_title'];
        $ws_endpoint = $attributes['ws_endpoint']['option_title'];

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

         $shippingcity = $location->getCity();
        $shippingstreet = $location->getAddress();
        $shippingpostcode = ""; //$location->getZip();      
        $shippingtelephone = $location->getPhone();

        $xml_header = '<order></order>';
        $xml = new \SimpleXMLElement($xml_header);

        // delivery
        $subnode_delivery = $xml->addChild('delivery');
        $subnode_delivery->addChild('name',  $location_name);
        $subnode_delivery->addChild('company', 'Best Health Food Shop');
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
        $subnode_shopper->addChild('yourreference', $orderIncrementId);
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

        $soapOrderClient = $this->soapClientFactory->create($ws_endpoint, array('trace' => 1));

        // Prepare SoapHeader parameters
        $sh_param = array('AuthenticationToken' => $authenticationToken, 'ErrorMessage' => '');
        $headers = new \SoapHeader('http://services.clfdistribution.com/CLFWebOrdering', 'WebServiceHeader', $sh_param);
        // Prepare Soap Client
        $soapOrderClient->__setSoapHeaders(array($headers));

        // Setup the PlaceCLFOrder parameters
        $order_param = array(
             'orderXml' => $xml->asXML());
        // 
        $error = 0;
        try
        {
            $res = $soapOrderClient->PlaceCLFOrder($order_param);
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

        echo "<pre>", htmlspecialchars($soapOrderClient->__getLastResponse()), "</pre>";

        $page = $this->resultPageFactory->create();
        $block = $page->getLayout()->getBlock('index.index');
        $block->setData('orderIncrementId', $orderIncrementId);
        $block->setData('shipping_method', $shipping_method);
        $block->setData('shipping_description', $shipping_description);
        $block->setData('authenticationToken', $authenticationToken);
        $block->setData('xml', $xml->asXML());

        $block->setData('clf_username', $clf_username);
        $block->setData('ws_endpoint', $ws_endpoint);
        $block->setData('clf_order_result', $clf_order_result);

        return $page;
    }
}

