<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model\Checkout\Subscription;

use Amasty\RecurringPayments\Api\FeeRepositoryInterface;
use Klarna\Core\Api\BuilderInterface;
use Magento\Quote\Api\Data\CartInterface;

class Fee
{
    /**
     * Order line code name
     *
     * @var string
     */
    protected $code;

    /**
     * Order line is used to calculate a total
     *
     * For example, shipping total, order total, or discount total
     *
     * This should be set to false for items like order items
     *
     * @var bool
     */
    protected $isTotalCollector = true;

    /**
     * @var FeeRepositoryInterface
     */
    private $feeRepository;

    public function __construct(FeeRepositoryInterface $feeRepository)
    {
        $this->feeRepository = $feeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(BuilderInterface $checkout)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $checkout->getObject();

        if ($quote instanceof CartInterface) {
            $fee = $this->feeRepository->getByQuoteId($quote->getId());

            if ($fee->getBaseAmount()) {
                $checkout->addData(
                    [
                        'subscription_unit_price' => $this->toApiFloat($fee->getBaseAmount()),
                        'subscription_total_amount' => $this->toApiFloat($fee->getBaseAmount()),
                    ]
                );
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function fetch(BuilderInterface $checkout)
    {
        if (isset($checkout['subscription_unit_price'])) {
            $checkout->addOrderLine(
                [
                    'name' => __('Initial Subscription Fee'),
                    'unit_price' => $checkout['subscription_unit_price'],
                    'total_amount' => $checkout['subscription_total_amount'],
                    'quantity' => 1
                ]
            );
        }

        return $this;
    }

    /**
     * Check if the order line is for an order item or a total collector
     *
     * @return boolean
     */
    public function isIsTotalCollector()
    {
        return $this->isTotalCollector;
    }

    /**
     * Retrieve code name
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set code name
     *
     * @param string $code
     *
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Prepare float for API call
     *
     * @param float $float
     *
     * @return int
     */
    private function toApiFloat($float)
    {
        return round($float * 100);
    }
}
