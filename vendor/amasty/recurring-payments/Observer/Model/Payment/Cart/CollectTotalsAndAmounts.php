<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Observer\Model\Payment\Cart;

use Amasty\RecurringPayments\Api\FeeRepositoryInterface;
use Magento\Framework\Event\{Observer, ObserverInterface};

/**
 * Class CollectTotalsAndAmounts
 */
class CollectTotalsAndAmounts implements ObserverInterface
{
    /**
     * @var FeeRepositoryInterface
     */
    private $feeRepository;

    public function __construct(FeeRepositoryInterface $feeRepository)
    {
        $this->feeRepository = $feeRepository;
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        $cart = $observer->getData('cart');

        $id = $cart->getSalesModel()->getDataUsingMethod('entity_id')
            ?? $cart->getSalesModel()->getDataUsingMethod('quote_id');

        $fee = $this->feeRepository->getByQuoteId($id);

        if ($fee->getBaseAmount()) {
            $cart->addCustomItem(
                (string)__('Initial Subscription Fee'),
                1,
                $fee->getBaseAmount()
            );
        }
    }
}
