<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ExitPopup
 */


namespace Amasty\ExitPopup\Model;

use Amasty\Base\Model\ConfigProviderAbstract;
use Magento\Newsletter\Model\Subscriber;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class ConfigProvider extends ConfigProviderAbstract
{
    /**
     * xpath prefix of module (section)
     */
    protected $pathPrefix = 'amasty_exit_popup/';

    /**#@+
     * xpath group parts
     */
    const GENERAL_BLOCK = 'general/';
    const PROMO_SETTINGS_BLOCK = 'promo_settings/';
    const EMAIL_SETTINGS_BLOCK = 'email_settings/';
    /**#@-*/

    /**#@+
     * xpath field parts
     */
    const EXIT_POPUP_ENABLED = 'enable';

    const EXIT_POPUP_TITLE = 'title';
    const EXIT_POPUP_TEXT = 'text';
    const EXIT_POPUP_PAGES = 'pages';
    const RECOVERY_TIME = 'recovery_time';
    const CUSTOM_TIME = 'custom_time';

    const PROMO_TYPE_FIELD = 'promo_type';
    const RULE_ID_FIELD = 'rule_id';
    const PRODUCT_ID = 'downloadable_product_id';
    const ASK_ENABLED = 'enable_ask';
    const CONSENT_MESSAGE = 'consent_message';

    const SENDER_EMAIL = 'email_sender';
    const EMAIL_TEMPLATE = 'template';
    /**#@-*/

    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($scopeConfig);
    }

    /**
     * @return bool
     */
    public function isExitPopupEnabled()
    {
        return $this->isSetFlag(self::GENERAL_BLOCK . self::EXIT_POPUP_ENABLED);
    }

    /**
     * @return string
     */
    public function getExitPopupTitle()
    {
        return (string)$this->getValue(self::GENERAL_BLOCK . self::EXIT_POPUP_TITLE);
    }

    /**
     * @return string
     */
    public function getExitPopupText()
    {
        return (string)$this->getValue(self::GENERAL_BLOCK . self::EXIT_POPUP_TEXT);
    }

    /**
     * @return array
     */
    public function getPopupPages()
    {
        if (!is_null($this->getValue(self::GENERAL_BLOCK . self::EXIT_POPUP_PAGES))) {
            return explode(',', $this->getValue(self::GENERAL_BLOCK . self::EXIT_POPUP_PAGES));
        }

        return [];
    }

    /**
     * @return int
     */
    public function getRecoveryTime()
    {
        return $this->getValue(self::GENERAL_BLOCK . self::RECOVERY_TIME);
    }

    /**
     * @return int
     */
    public function getCustomTime()
    {
        return $this->getValue(self::GENERAL_BLOCK . self::CUSTOM_TIME);
    }

    /**
     * @return string
     */
    public function getPromoType()
    {
        return $this->getValue(self::PROMO_SETTINGS_BLOCK . self::PROMO_TYPE_FIELD);
    }

    /**
     * @return int
     */
    public function getRuleId()
    {
        return $this->getValue(self::PROMO_SETTINGS_BLOCK . self::RULE_ID_FIELD);
    }

    /**
     * @return string
     */
    public function getProductId()
    {
        return $this->getValue(self::PROMO_SETTINGS_BLOCK . self::PRODUCT_ID);
    }

    /**
     * @return bool
     */
    public function isAskEnabled()
    {
        return $this->isSetFlag(self::PROMO_SETTINGS_BLOCK . self::ASK_ENABLED);
    }

    /**
     * @return string
     */
    public function getConsentMessage()
    {
        return $this->getValue(self::PROMO_SETTINGS_BLOCK . self::CONSENT_MESSAGE);
    }

    /**
     * @return string
     */
    public function getEmailSender()
    {
        return $this->getValue(self::EMAIL_SETTINGS_BLOCK . self::SENDER_EMAIL);
    }

    /**
     * @return string
     */
    public function getEmailTemplate()
    {
        return $this->getValue(self::EMAIL_SETTINGS_BLOCK . self::EMAIL_TEMPLATE);
    }

    /**
     * @return bool
     */
    public function allowGuestSubscribe()
    {
        return (bool)$this->scopeConfig->getValue(
            Subscriber::XML_PATH_ALLOW_GUEST_SUBSCRIBE_FLAG,
            ScopeInterface::SCOPE_STORE
        );
    }
}
