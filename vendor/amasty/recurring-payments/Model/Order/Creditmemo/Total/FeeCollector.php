<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model\Order\Creditmemo\Total;

use Amasty\RecurringPayments\Api\FeeRepositoryInterface;
use Amasty\RecurringPayments\Model\Fee;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Framework\Exception\NoSuchEntityException;

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
     * @param Creditmemo $creditmemo
     *
     * @return $this
     */
    public function collect(Creditmemo $creditmemo)
    {
        try {
            /** @var Order $order */
            $order = $creditmemo->getOrder();

            try {
                /** @var Fee $fee */
                $fee = $this->feeRepository->getByQuoteId($order->getQuoteId());

                $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $fee->getAmount());
                $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $fee->getBaseAmount());
            } catch (\Exception $exception) {
                ; // This is fine
            }
        } catch (NoSuchEntityException $exception) {
            return $this;
        }

        return $this;
    }
}
