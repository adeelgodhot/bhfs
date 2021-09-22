<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPaypal
 */


declare(strict_types=1);

namespace Amasty\RecurringPaypal\Model;

use Amasty\RecurringPayments\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\Encryptor;

class ConfigProvider extends Config
{
    const WEBHOOK_ID_XML_PATH = 'amasty_recurring_payments/paypal/webhook_id';
    const EMAIL_TEMPLATE_SUBSCRIPTION_RENEWAL = 'email_template_paypal_renewal';
    const SANDBOX_FLAG_PATH = 'paypal/wpp/sandbox_flag';

    const LIVE_MODE = 'LIVE';
    const SANDBOX_MODE = 'SANDBOX';

    /**
     * @var Encryptor
     */
    private $encryptor;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Encryptor $encryptor
    ) {
        parent::__construct($scopeConfig);
        $this->encryptor = $encryptor;
    }

    /**
     * Returns pair of keys if both are filled and null otherwise
     * @return array|null
     */
    public function getPaypalCredentials()
    {
        $clientId = $this->getValue('paypal/client_id');
        $clientSecret = $this->encryptor->decrypt($this->getValue('paypal/client_secret'));

        if (empty($clientId) || empty($clientSecret)) {
            return null;
        }

        return [$clientId, $clientSecret];
    }

    /**
     * @return string
     */
    public function getPaypalWebhookId(): string
    {
        return (string)$this->getValue('paypal/webhook_id');
    }

    /**
     * @param int $storeId
     * @return string
     */
    public function getRenewalEmailTemplate(int $storeId): string
    {
        return (string)$this->getValue(
            self::EMAIL_NOTIFICATION_BLOCK . self::EMAIL_TEMPLATE_SUBSCRIPTION_RENEWAL,
            $storeId
        );
    }

    /**
     * @return string
     */
    public function getPaymentMode()
    {
        return $this->scopeConfig->getValue(self::SANDBOX_FLAG_PATH) ? self::SANDBOX_MODE : self::LIVE_MODE;
    }
}
