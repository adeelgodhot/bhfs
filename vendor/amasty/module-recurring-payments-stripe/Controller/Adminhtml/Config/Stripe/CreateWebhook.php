<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringStripe
 */


declare(strict_types=1);

namespace Amasty\RecurringStripe\Controller\Adminhtml\Config\Stripe;

use Amasty\RecurringStripe\Model\Adapter;
use Amasty\RecurringStripe\Model\ConfigWebhook;
use Amasty\Stripe\Model\Ui\ConfigProvider;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Cache\Type\Config;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use \Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Url;

class CreateWebhook extends Action
{
    /**
     * @var Adapter
     */
    private $adapter;

    /**
     * @var Url
     */
    private $urlBuilder;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var TypeListInterface
     */
    private $cacheTypeList;

    /**
     * @var ConfigInterface
     */
    private $configResource;

    /**
     * @var ReinitableConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        Context $context,
        Adapter $adapter,
        Url $urlBuilder,
        ConfigInterface $configResource,
        EncryptorInterface $encryptor,
        TypeListInterface $cacheTypeList,
        ReinitableConfigInterface $scopeConfig
    ) {
        parent::__construct($context);
        $this->adapter = $adapter;
        $this->urlBuilder = $urlBuilder;
        $this->encryptor = $encryptor;
        $this->cacheTypeList = $cacheTypeList;
        $this->configResource = $configResource;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Create webhook for stripe
     *
     * @inheritDoc
     */
    public function execute()
    {
        $url = $this->urlBuilder->getUrl('amasty_recurring/stripe/index', ['_nosid' => true]);
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        try {
            /** @var \Stripe\WebhookEndpoint $webhook */
            $webhook = $this->adapter->webhookCreate($url);
        } catch (\Stripe\Error\Authentication $exception) {
            $this->messageManager->addExceptionMessage($exception);

            return $resultRedirect->setRefererUrl();
        }

        $this->configResource->saveConfig(
            'payment/' . ConfigProvider::CODE . '/' . ConfigWebhook::WEBHOOK_SECRET,
            $this->encryptor->encrypt($webhook->secret)
        );

        $this->cacheTypeList->invalidate(Config::TYPE_IDENTIFIER);
        $this->scopeConfig->reinit();

        return $resultRedirect->setRefererUrl();
    }
}
