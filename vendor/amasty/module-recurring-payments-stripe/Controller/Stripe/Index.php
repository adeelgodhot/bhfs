<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringStripe
 */


declare(strict_types=1);

namespace Amasty\RecurringStripe\Controller\Stripe;

use Amasty\RecurringStripe\Api\IpnInterface;
use Amasty\RecurringStripe\Model\Adapter;
use Amasty\RecurringStripe\Model\ConfigWebhook;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Data\Form\FormKey;
use Psr\Log\LoggerInterface;

class Index extends Action
{
    /**
     * @var Adapter
     */
    private $adapter;

    /**
     * @var ConfigWebhook
     */
    private $configWebhook;

    /**
     * @var IpnInterface
     */
    private $stripeIpn;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Context $context,
        FormKey $formKey,
        Adapter $adapter,
        ConfigWebhook $configWebhook,
        IpnInterface $stripeIpn,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->setFormKey($formKey);

        $this->adapter = $adapter;
        $this->configWebhook = $configWebhook;
        $this->stripeIpn = $stripeIpn;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Raw $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        /** @var \Magento\Framework\App\RequestInterface $request */
        $request = $this->getRequest();

        $payload = $request->getContent();
        $sigHeader = $request->getServer('HTTP_STRIPE_SIGNATURE');
        $event = null;

        try {
            /** @var \Stripe\Event $event */
            $event = $this->adapter->eventRetrieve(
                (string)$payload,
                (string)$sigHeader,
                $this->configWebhook->getWebhookSecret()
            );
            $this->stripeIpn->processIpnRequest($event);
            $result->setHttpResponseCode(200);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            $result->setHttpResponseCode(400);
        }
        $result->setContents(''); // Prevent fatal error on Magento 2.3.3

        return $result;
    }

    /**
     * @param FormKey $formKey
     */
    private function setFormKey(FormKey $formKey)
    {
        /** @var \Magento\Framework\App\RequestInterface $request */
        $request = $this->getRequest();

        if (empty($request->getParam('form_key'))) {
            $request->setParam('form_key', $formKey->getFormKey());
        }
    }
}
