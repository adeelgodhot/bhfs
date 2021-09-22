<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Block\Sales\Order;

use Amasty\RecurringPayments\Api\FeeRepositoryInterface;
use Amasty\RecurringPayments\Model\Fee as FeeModel;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Block\Adminhtml\Order\Totals;
use Magento\Sales\Model\Order;

/**
 * Class Fee
 */
class Fee extends AbstractBlock
{
    /**
     * @var FeeRepositoryInterface
     */
    private $feeRepository;

    public function __construct(
        Context $context,
        FeeRepositoryInterface $feeRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->feeRepository = $feeRepository;
    }

    /**
     * @return $this
     */
    public function initTotals()
    {
        /** @var Totals $parent */
        $parent = $this->getParentBlock();

        if (!$parent || !method_exists($parent, 'getOrder')) {
            return $this;
        }

        /** @var Order $order */
        $order = $parent->getOrder();

        if (!($order instanceof OrderInterface)) {
            return $this;
        }

        $quoteId = $order->getQuoteId();

        /** @var FeeModel $fee */
        $fee = $this->feeRepository->getByQuoteId($quoteId);

        if ($fee->getAmount()) {
            $total = new \Magento\Framework\DataObject(
                [
                    'code' => $this->getNameInLayout(),
                    'label' => __('Initial Subscription Fee'),
                    'value' => $fee->getAmount(),
                    'base_value' => $fee->getBaseAmount()
                ]
            );

            $parent->addTotalBefore($total, 'grand_total');
        }

        return $this;
    }
}
