<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ExitPopup
 */


namespace Amasty\ExitPopup\Model;

use Amasty\ExitPopup\Api\PopupManagementInterface;
use Magento\Framework\Api\AbstractSimpleObject;
use Amasty\ExitPopup\Model\Config\Source\PagesSource;
use Amasty\ExitPopup\Model\ConfigProvider;
use Amasty\ExitPopup\Model\Config\Source\TimeSource;
use Magento\Store\Model\StoreManagerInterface;

class PopupManagement extends AbstractSimpleObject implements PopupManagementInterface
{
    /**#@+*/
    const FIFTEEN_MIN_IN_SEC = 900;
    const THIRTY_MIN_IN_SEC = 1800;
    const ONE_HOUR_IN_SEC = 3600;
    const TWO_HOURS_IN_SEC = 7200;
    const FOUR_HOURS_IN_SEC = 14400;
    const EIGHT_HOURS_IN_SEC = 28800;
    const ONE_DAY_IN_SEC = 86400;
    const ONE_WEEK_IN_SEC = 604800;
    /**#@-*/

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        ConfigProvider $configProvider,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->configProvider = $configProvider;
        $this->storeManager = $storeManager;
        parent::__construct($data);
    }

    /**
     * {@inheritdoc}
     */
    public function getPopupData()
    {
        return [
            'popup' => [
                'title' => $this->configProvider->getExitPopupTitle(),
                'text' => $this->configProvider->getExitPopupText(),
                'ask' => $this->configProvider->isAskEnabled(),
                'consent' => $this->configProvider->getConsentMessage(),
                'delayInSeconds' => $this->getRecoveryTime(),
                'storeCode' => $this->storeManager->getStore()->getCode()
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function isVisible($pageValue)
    {
        switch ($pageValue) {
            case PagesSource::CART_VALUE:
                $result = in_array(PagesSource::CART_VALUE, $this->configProvider->getPopupPages());
                break;
            case PagesSource::CHECKOUT_VALUE:
                $result = in_array(PagesSource::CHECKOUT_VALUE, $this->configProvider->getPopupPages());
                break;
            case PagesSource::PAYPAL_VALUE:
                $result = in_array(PagesSource::PAYPAL_VALUE, $this->configProvider->getPopupPages());
                break;
            default:
                $result = false;
                break;
        }

        return $result;
    }

    /**
     * @return int
     */
    private function getRecoveryTime()
    {
        switch ($this->configProvider->getRecoveryTime()) {
            case TimeSource::FIFTEEN_MINUTES:
                $result = self::FIFTEEN_MIN_IN_SEC;
                break;
            case TimeSource::THIRTY_MINUTES:
                $result = self::THIRTY_MIN_IN_SEC;
                break;
            case TimeSource::ONE_HOUR:
                $result = self::ONE_HOUR_IN_SEC;
                break;
            case TimeSource::TWO_HOURS:
                $result = self::TWO_HOURS_IN_SEC;
                break;
            case TimeSource::FOUR_HOURS:
                $result = self::FOUR_HOURS_IN_SEC;
                break;
            case TimeSource::EIGHT_HOURS:
                $result = self::EIGHT_HOURS_IN_SEC;
                break;
            case TimeSource::ONE_DAY:
                $result = self::ONE_DAY_IN_SEC;
                break;
            case TimeSource::ONE_WEEK:
                $result = self::ONE_WEEK_IN_SEC;
                break;
            case TimeSource::CUSTOM:
                $result = $this->configProvider->getCustomTime() * 60;
                break;
            default:
                $result = self::ONE_DAY_IN_SEC;
                break;
        }

        return $result;
    }
}
