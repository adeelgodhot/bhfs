<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ExitPopup
 */


namespace Amasty\ExitPopup\Block\Component;

use Magento\Checkout\Block\Cart\AbstractCart;
use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Model\Session as CheckoutSession;
use Amasty\ExitPopup\Api\PopupManagementInterface;
use Amasty\ExitPopup\Model\ConfigProvider;

class Popup extends AbstractCart
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
        Context $context,
        CustomerSession $customerSession,
        CheckoutSession $checkoutSession,
        ConfigProvider $configProvider,
        array $data = []
    ) {
        $this->popupManagement = $popupManagement;
        $this->configProvider = $configProvider;
        parent::__construct($context, $customerSession, $checkoutSession, $data);
    }

    /**
     * @inheritdoc
     */
    public function getJsLayout()
    {
        if ($this->configProvider->isExitPopupEnabled()
            && $this->popupManagement->isVisible($this->getData('scope'))) {
            $this->jsLayout['components']['amasty-exit-popup-component']
                += $this->popupManagement->getPopupData();
        } else {
            unset($this->jsLayout['components']['amasty-exit-popup-component']);
        }

        return parent::getJsLayout();
    }
}
