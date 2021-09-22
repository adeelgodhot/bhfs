<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */

namespace Amasty\Followup\Controller\Email;

class Url extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Magento\Framework\Url\Decoder
     */
    protected $decoder;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Url\Decoder $decoder
    ) {
        parent::__construct($context);
        $this->decoder = $decoder;
    }

    protected function _getHistory()
    {
        $ret = null;

        $id = $this->getRequest()->getParam('id');
        $key = $this->getRequest()->getParam('key');

        $historyResource = $this->_objectManager->create('Amasty\Followup\Model\ResourceModel\History\Collection')
            ->addFieldToFilter('main_table.history_id', $id);


        if ($historyResource->getSize() > 0)
        {
            $items = $historyResource->getItems();
            $history = end($items);

            if ($history->getId() && $history->getPublicKey() == $key){
                $ret = $history;
            }
        }
        return $ret;
    }

    public function execute()
    {
        $url = $this->getRequest()->getParam('url');
        $mageUrl = $this->getRequest()->getParam('mageUrl');

        $history = $this->_getHistory();

        if ($history && ($url || $mageUrl)){

            $target = null;

            if ($url){
                $target = $this->decoder->decode($url);
            } else if ($mageUrl){
                $target = $this->_url->getUrl($this->decoder->decode($mageUrl));
            }

            $this->_loginCustomer($history);

            $link = $this->_objectManager->get('Amasty\Followup\Model\Link');
            $link->setData(array(
                "customer_id" => $history->getCustomerId(),
                "history_id" => $history->getId(),
                "link" => $target
            ));
            $link->save();

            $this->getResponse()->setRedirect($target);
        } else {
            $this->_forward('defaultNoRoute');
        }

    }

    protected function _loginCustomer($history)
    {
        $customerSession = $this->_objectManager->get('Magento\Customer\Model\Session');
        $checkoutSession = $this->_objectManager->get('Magento\Checkout\Model\Session');

        if ($customerSession->isLoggedIn()){
            if ($history->getCustomerId() != $customerSession->getCustomerId()){
                $customerSession->logout();
            }
        }

        // customer. login
        if ($history->getCustomerId()){

            $customer = $this->_objectManager->get('Magento\Customer\Model\Customer')->load($history->getCustomerId());

            if ($customer->getId()) {
                $customerSession->setCustomerAsLoggedIn($customer);
            }
        }
        elseif ($history->getQuoteId()){
            //visitor. restore quote in the session
            $quote = $this->_objectManager->get('Magento\Quote\Model\Quote')->load($history->getQuoteId());

            if ($quote){
                $checkoutSession->replaceQuote($quote);
                $quote->getBillingAddress()->setEmail($history->getEmail());
            }
        }

        if ($history->getSalesRuleCoupon()){

            $code = $history->getSalesRuleCoupon();
            $quote = $checkoutSession->getQuote();
            if ($code && $quote){

                $quote->setCouponCode($code)
                    ->collectTotals()
                    ->save();
            }
        }

    }
}