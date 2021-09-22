<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPaypal
 */


declare(strict_types=1);

namespace Amasty\RecurringPaypal\Controller\Paypal;

use Amasty\RecurringPaypal\Api\WebHook\ProcessorInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Data\Form\FormKey;
use Psr\Log\LoggerInterface;

class Webhook extends Action
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ProcessorInterface
     */
    private $webHookProcessor;

    public function __construct(
        Context $context,
        FormKey $formKey,
        ProcessorInterface $webHookProcessor,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->setFormKey($formKey);
        $this->logger = $logger;
        $this->webHookProcessor = $webHookProcessor;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Raw $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        /** @var RequestInterface $request */
        $request = $this->getRequest();

        try {
            $this->webHookProcessor->processRequest($request);
            $result->setHttpResponseCode(200);
        } catch (\RuntimeException $e) {
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
        /** @var RequestInterface $request */
        $request = $this->getRequest();

        if (empty($request->getParam('form_key'))) {
            $request->setParam('form_key', $formKey->getFormKey());
        }
    }
}
