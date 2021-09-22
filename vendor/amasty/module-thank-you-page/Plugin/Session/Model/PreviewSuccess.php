<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ThankYouPage
 */


namespace Amasty\ThankYouPage\Plugin\Session\Model;

use Amasty\ThankYouPage\Model\Config;
use Magento\Backend\Model\Auth\Session as AuthSession;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Checkout\Model\Session\SuccessValidator;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Config\CacheInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Model\Order as SalesOrder;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

class PreviewSuccess
{
    const CHECK_PARAM = 'oneTimeSecretKey';
    const PREVIEW_KEY = 'amasty_thank_you_page_key';

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var CollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    public function __construct(
        RequestInterface $request,
        CheckoutSession $checkoutSession,
        CollectionFactory $orderCollectionFactory,
        Config $config,
        CacheInterface $cache,
        ManagerInterface $messageManager
    ) {
        $this->request = $request;
        $this->checkoutSession = $checkoutSession;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->config = $config;
        $this->cache = $cache;
        $this->messageManager = $messageManager;
    }

    /**
     * @param SuccessValidator $subject
     * @param bool $result
     *
     * @return bool
     */
    public function afterIsValid(SuccessValidator $subject, $result)
    {
        if ($this->isValidCheckParam()) {
            $this->cache->remove(self::PREVIEW_KEY);

            $incrementId = $this->config->getOrderIncrementId();
            if (!$incrementId) {
                $order = $this->getLastOrder();
            } else {
                $incrementId = str_replace('#', '', $incrementId);
                $order = $this->getOrderByIncrementId($incrementId);
            }

            /** @var SalesOrder $order */
            if ($order->getId()) {
                $this->checkoutSession
                    ->setLastOrderId($order->getId())
                    ->setLastRealOrderId($order->getIncrementId())
                    ->setLastOrderStatus($order->getStatus());

                return true;
            }
        } elseif ($this->request->getParam(self::CHECK_PARAM)) {
            $this->messageManager->addErrorMessage(
                __('Due to safety concerns, the link has a short lifetime and can be used only once.'
                    . ' If you need to see this page once more,'
                    . ' go to the extension configuration page and press the Preview button again.')
            );
        }

        return $result;
    }

    /**
     * @return bool
     */
    private function isValidCheckParam()
    {
        return $this->request->getParam(self::CHECK_PARAM) === $this->cache->load(self::PREVIEW_KEY);
    }

    /**
     * @param int $incrementId
     *
     * @return SalesOrder
     */
    private function getOrderByIncrementId($incrementId)
    {
        $orderCollection = $this->getOrderCollection();
        $orderCollection->addFieldToFilter('increment_id', $incrementId)
            ->setPageSize(1)
            ->setCurPage(1);

        return $orderCollection->getLastItem();
    }

    /**
     * @return SalesOrder
     */
    private function getLastOrder()
    {
        $orderCollection = $this->getOrderCollection();
        $orderCollection->setPageSize(1)
            ->setOrder('entity_id', SortOrder::SORT_DESC);

        return $orderCollection->getLastItem();
    }

    /**
     * @return Collection
     */
    private function getOrderCollection()
    {
        return $this->orderCollectionFactory->create();
    }
}
