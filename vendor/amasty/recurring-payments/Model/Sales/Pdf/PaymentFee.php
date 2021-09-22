<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model\Sales\Pdf;

use Amasty\RecurringPayments\Api\FeeRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\Order\Pdf\Total\DefaultTotal;
use Magento\Tax\Helper\Data;
use Magento\Tax\Model\Calculation;
use Magento\Tax\Model\ResourceModel\Sales\Order\Tax\CollectionFactory;

class PaymentFee extends DefaultTotal
{
    /**
     * @var FeeRepositoryInterface
     */
    private $feeRepository;

    public function __construct(
        Data $taxHelper,
        Calculation $taxCalculation,
        CollectionFactory $ordersFactory,
        FeeRepositoryInterface $feeRepository,
        array $data = []
    ) {
        parent::__construct($taxHelper, $taxCalculation, $ordersFactory, $data);
        $this->feeRepository = $feeRepository;
    }

    /**
     * @inheritDoc
     */
    public function getAmount()
    {
        try {
            /** @var \Amasty\RecurringPayments\Api\Data\FeeInterface $paymentFee */
            $paymentFee = $this->feeRepository->getByQuoteId($this->getSource()->getOrder()->getQuoteId());

            return $paymentFee->getAmount();
        } catch (NoSuchEntityException $exception) {
            return null;
        }
    }
}
