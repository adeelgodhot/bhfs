<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPaypal
 */


namespace Amasty\RecurringPaypal\Controller\Adminhtml\Config\Paypal;

use Amasty\RecurringPaypal\Model\Api\Adapter;
use Amasty\RecurringPaypal\Model\ConfigProvider;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\Url;
use PayPal\Exception\PayPalConnectionException;

class CreateWebhook extends Action
{
    const VALIDATION_ERROR_CODE = 'VALIDATION_ERROR';
    const WEBHOOK_EVENTS = [
        'PAYMENT.SALE.COMPLETED',
        'BILLING.SUBSCRIPTION.CREATED',
        'BILLING.SUBSCRIPTION.CANCELLED',
        'BILLING.SUBSCRIPTION.RE-ACTIVATED',
        'BILLING.SUBSCRIPTION.SUSPENDED',
        'BILLING.SUBSCRIPTION.UPDATED',
        'BILLING.SUBSCRIPTION.ACTIVATED',
        'BILLING.SUBSCRIPTION.EXPIRED',
        'BILLING.SUBSCRIPTION.RENEWED'
    ];

    /**
     * @var Adapter
     */
    private $adapter;

    /**
     * @var Url
     */
    private $urlBuilder;

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
        ReinitableConfigInterface $scopeConfig
    ) {
        parent::__construct($context);
        $this->adapter = $adapter;
        $this->urlBuilder = $urlBuilder;
        $this->configResource = $configResource;
        $this->scopeConfig = $scopeConfig;
    }

    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        try {
            $id = $this->adapter->createWebhook($this->getWebhookUrl(), self::WEBHOOK_EVENTS);
        } catch (PayPalConnectionException $e) {
            $this->handlePaypalException($e);

            return $resultRedirect->setRefererUrl();
        } catch (\Exception $exception) {
            $this->messageManager->addExceptionMessage($exception);

            return $resultRedirect->setRefererUrl();
        }

        $this->configResource->saveConfig(ConfigProvider::WEBHOOK_ID_XML_PATH, $id);

        $this->scopeConfig->reinit();

        return $resultRedirect->setRefererUrl();
    }

    protected function getWebhookUrl(): string
    {
        $url = $this->urlBuilder->getUrl('amasty_recurring/paypal/webhook', ['_nosid' => true]);

        return str_replace('http://', 'https://', $url); // Force https because paypal fails on http urls anyway
    }

    protected function handlePaypalException(PayPalConnectionException $e)
    {
        $data = json_decode($e->getData(), true);
        $message = null;
        if (isset($data['name']) && $data['name'] == self::VALIDATION_ERROR_CODE) {
            $message = __(
                'Please ensure that your store url (%1) is available for public access'
                . ' and supports secure connections (HTTPS)',
                $this->getWebhookUrl()
            );
        } elseif (isset($data['message'])) {
            $message = $data['message'];
        } elseif (isset($data['error_description'])) {
            $message = $data['error_description'];
        }

        if ($message) {
            $this->messageManager->addErrorMessage(__('Unable to create webhook: %1', $message));
        } else {
            $this->messageManager->addExceptionMessage($e);
        }
    }
}
