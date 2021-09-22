<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Plugin\Sales\Model\Service;

use Amasty\GiftCardAccount\Model\ConfigProvider;
use Amasty\GiftCardAccount\Model\RefundStrategy;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Service\CreditmemoService;
use Psr\Log\LoggerInterface;

class CreditmemoServicePlugin
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var RefundStrategy
     */
    private $refundStrategy;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        ConfigProvider $configProvider,
        RefundStrategy $refundStrategy,
        LoggerInterface $logger
    ) {
        $this->configProvider = $configProvider;
        $this->refundStrategy = $refundStrategy;
        $this->logger = $logger;
    }

    public function afterRefund(
        CreditmemoService $subject,
        Creditmemo $result
    ) {
        $order = $result->getOrder();
        $storeId = (int)$order->getStore()->getId();

        try {
            if ($this->configProvider->isEnabled($storeId)
                && $this->configProvider->isRefundAllowed($storeId)
            ) {
                $this->refundStrategy->refundToAccount($result);
            }
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }

        return $result;
    }
}
