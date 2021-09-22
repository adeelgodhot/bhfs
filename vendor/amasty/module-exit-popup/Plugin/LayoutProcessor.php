<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ExitPopup
 */


namespace Amasty\ExitPopup\Plugin;

use Amasty\ExitPopup\Api\PopupManagementInterface;
use Amasty\ExitPopup\Model\Config\Source\PagesSource;
use Amasty\ExitPopup\Model\ConfigProvider;

class LayoutProcessor
{
    /**
     * @var PopupManagementInterface
     */
    private $popupManagement;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        PopupManagementInterface $popupManagement,
        ConfigProvider $configProvider
    ) {
        $this->popupManagement = $popupManagement;
        $this->configProvider = $configProvider;
    }

    /**
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $subject
     * @param array $result
     *
     * @return array
     */
    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        $result
    ) {
        if (
            $this->configProvider->isExitPopupEnabled()
            && $this->popupManagement->isVisible(PagesSource::CHECKOUT_VALUE)
        ) {
            $result['components']['checkout']['children']['sidebar']['children']['summary']['children']['amasty-exitpopup-template'] +=
                $this->popupManagement->getPopupData();
        } else {
            unset($result['components']['checkout']['children']['sidebar']['children']['summary']['children']['amasty-exitpopup-template']);
        }

        return $result;
    }
}