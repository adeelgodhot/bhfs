<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringStripe
 */


declare(strict_types=1);

namespace Amasty\RecurringStripe\Plugin\Gateway\Http\Client;

use Amasty\RecurringStripe\Plugin\Gateway\Helper\AmountHelper;
use Amasty\Stripe\Gateway\Http\Client\AbstractClient;
use Amasty\Stripe\Gateway\Http\TransferFactory;
use Amasty\Stripe\Model\Adapter\StripeAdapter;
use Magento\Payment\Gateway\Http\TransferInterface;

class AbstractClientPlugin
{
    /**
     * @var TransferFactory
     */
    private $transferFactory;

    /**
     * @var StripeAdapter
     */
    private $stripeAdapter;

    public function __construct(TransferFactory $transferFactory, StripeAdapter $stripeAdapter)
    {
        $this->transferFactory = $transferFactory;
        $this->stripeAdapter = $stripeAdapter;
    }

    /**
     * @param AbstractClient $subject
     * @param \Closure $proceed
     * @param TransferInterface $transferObject
     *
     * @return mixed
     */
    public function aroundPlaceRequest(
        AbstractClient $subject,
        \Closure $proceed,
        TransferInterface $transferObject
    ) {
        $needRefund = false;
        $needCancel = false;

        if ($body = $transferObject->getBody()) {
            if (!isset($body['chargeId'])
                && isset($body['amount'])
                && $body['amount'] == AmountHelper::STRIPE_MIN_AMOUNT
            ) {
                $body['description'] = __('This is a test payment for authorization purposes to verify the validity '
                    . 'of the card. Refund is issued immediately but its processing may take some time.');
                $transferObject = $this->transferFactory->create($body);
                if ($body['capture']) {
                    $needRefund = true;
                } else {
                    $needCancel = true;
                }
            }
        }
        $result = $proceed($transferObject);

        if ($needRefund && isset($result['object'])) {
            $charge = $this->getCharge($result['object']);
            $this->createRefund($charge);
        }

        if ($needCancel && isset($result['object'])) {
            $result['object']->cancel();
        }

        return $result;
    }

    /**
     * @param \Stripe\PaymentIntent $paymentIntent
     *
     * @return \Stripe\Collection
     */
    private function getCharge(\Stripe\PaymentIntent $paymentIntent)
    {
        return \Stripe\Charge::all(
            [
                'payment_intent' => $paymentIntent->id,
                'limit' => 1,
            ]
        );
    }

    /**
     * @param \Stripe\Charge $charge
     */
    private function createRefund($charge)
    {
        $this->stripeAdapter->refundCreate(['charge' => $charge->data[0]->id]);
    }
}
