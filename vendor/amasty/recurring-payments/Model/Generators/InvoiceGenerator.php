<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model\Generators;

use Amasty\RecurringPayments\Api\Generators\InvoiceGeneratorInterface;
use Magento\Framework\DB\TransactionFactory;
use Magento\Sales\Api\Data\{InvoiceInterface, OrderInterface};
use Magento\Sales\Api\InvoiceManagementInterface;
use Magento\Sales\Model\Order\Invoice;

/**
 * Class InvoiceGenerator
 */
class InvoiceGenerator implements InvoiceGeneratorInterface
{
    /**
     * @var InvoiceManagementInterface
     */
    private $invoiceManagement;

    /**
     * @var TransactionFactory
     */
    private $transactionFactory;

    public function __construct(
        InvoiceManagementInterface $invoiceManagement,
        TransactionFactory $transactionFactory
    ) {
        $this->invoiceManagement = $invoiceManagement;
        $this->transactionFactory = $transactionFactory;
    }

    /**
     * @inheritDoc
     */
    public function generate(OrderInterface $order, string $transactionId = null): InvoiceInterface
    {
        /** @var Invoice $invoice */
        $invoice = $this->invoiceManagement->prepareInvoice($order);
        $invoice->setState(Invoice::STATE_PAID);
        $invoice->setTransactionId($transactionId);

        $this->invoiceOrder($invoice->getOrder());
        $transactionSave = $this->transactionFactory->create();

        $transactionSave->addObject(
            $invoice
        )->addObject(
            $invoice->getOrder()
        );

        $transactionSave->save();

        return $invoice;
    }

    /**
     * @param OrderInterface $order
     */
    private function invoiceOrder(OrderInterface $order)
    {
        $order->setTotalPaid($order->getGrandTotal());
        $order->setBaseTotalPaid($order->getBaseGrandTotal());

        $order->setTotalInvoiced($order->getGrandTotal());
        $order->setBaseTotalInvoiced($order->getBaseGrandTotal());

        $order->setSubtotalInvoiced($order->getSubtotal());
        $order->setBaseSubtotalInvoiced($order->getBaseSubtotal());

        $order->setTaxInvoiced($order->getTaxAmount());
        $order->setBaseTaxInvoiced($order->getBaseTaxAmount());

        $order->setDiscountTaxCompensationInvoiced($order->getDiscountTaxCompensationAmount());
        $order->setBaseDiscountTaxCompensationInvoiced($order->getBaseDiscountTaxCompensationAmount());

        $order->setShippingTaxInvoiced($order->getShippingTaxAmount());
        $order->setBaseShippingTaxInvoiced($order->getBaseShippingTaxAmount());

        $order->setShippingInvoiced($order->getShippingAmount());
        $order->setBaseShippingInvoiced($order->getBaseShippingAmount());

        $order->setDiscountInvoiced($order->getDiscountAmount());
        $order->setBaseDiscountInvoiced($order->getBaseDiscountAmount());
        $order->setBaseTotalInvoicedCost($order->getBaseCost());

        /** @var \Magento\Sales\Api\Data\OrderItemInterface $orderItem */
        foreach ($order->getItems() as $orderItem) {
            $orderItem->setRowInvoiced($orderItem->getRowTotal());
            $orderItem->setBaseRowInvoiced($orderItem->getBaseRowTotal());
            $orderItem->setDiscountInvoiced($orderItem->getDiscountAmount());
            $orderItem->setBaseDiscountInvoiced($orderItem->getBaseDiscountAmount());
            $orderItem->setTaxInvoiced($orderItem->getTaxAmount());
            $orderItem->setBaseTaxInvoiced($orderItem->getBaseTaxAmount());
            $orderItem->setQtyInvoiced($orderItem->getQtyOrdered());
        }
    }
}
