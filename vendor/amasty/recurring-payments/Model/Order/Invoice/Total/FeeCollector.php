<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model\Order\Invoice\Total;

use Amasty\RecurringPayments\Api\FeeRepositoryInterface;
use Amasty\RecurringPayments\Model\Fee;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Invoice\Total\AbstractTotal;

class FeeCollector extends AbstractTotal
{
    /**
     * @var FeeRepositoryInterface
     */
    private $feeRepository;

    public function __construct(
        FeeRepositoryInterface $feeRepository,
        array $data = []
    ) {
        parent::__construct($data);
        $this->feeRepository = $feeRepository;
    }

    /**
     * @param Invoice $invoice
     *
     * @return $this
     */
    public function collect(Invoice $invoice)
    {
        try {
            /** @var Order $order */
            $order = $invoice->getOrder();

            try {
                /** @var Fee $fee */
                $fee = $this->feeRepository->getByQuoteId($order->getQuoteId());

                $invoice->setGrandTotal($invoice->getGrandTotal() + $fee->getAmount());
                $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $fee->getBaseAmount());
            } catch (\Exception $exception) {
                ; // This is fine
            }
        } catch (NoSuchEntityException $exception) {
            return $this;
        }

        return $this;
    }
}
