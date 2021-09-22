<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingBar
 */


namespace Amasty\ShippingBar\Block\Frontend;

use Amasty\ShippingBar\Model\BarManagement;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Template;

class Bar extends Template
{
    /**
     * @var BarManagement
     */
    private $barManagement;

    /**
     * @var Session
     */
    private $customerSession;

    public function __construct(
        Template\Context $context,
        BarManagement $barManagement,
        Session $customerSession,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->barManagement = $barManagement;
        $this->customerSession = $customerSession;
    }

    /**
     * @return string
     */
    public function getJsLayout()
    {
        $customerGroup = $this->customerSession->getCustomerGroupId();
        $page = $this->barManagement->getPage($this->getRequest());
        $data = [];

        $data['currencySymbol'] = $this->_storeManager->getStore()->getCurrentCurrency()->getCurrencySymbol()
            ?: $this->_storeManager->getStore()->getCurrentCurrency()->getCurrencyCode();

        $profile = $this->barManagement->getFilledData(
            $customerGroup,
            $page,
            $this->barManagement->getPosition($this->getPosition())
        );

        if ($profile) {
            $data['actionClickable'] = $profile->getActionClickable();
            $data['closeable'] = $profile->getCloseable();
            $data['isCarVisibleValue'] = $profile->getCarIconVisible();
            $data['textSize'] = $profile->getTextSize();
            $data['fontFamily'] = $profile->getTextFont();
            $data['barBackground'] = $profile->getBackgroundColor();
            $data['extraColor'] = $profile->getExtraColor();
            $data['textColor'] = $profile->getTextColor();
            $data['actionLink'] = $profile->getActionLink();
            $data['goal'] = $profile->getGoal();
            $data['customStyle'] = $profile->getCustomStyle();
            $data['position'] = $profile->getPosition();
            $data['labels'] = $profile->getLabels();
        }

        $this->jsLayout['components']['amasty-shipbar-' . $this->getPosition()] += $data;

        return parent::getJsLayout();
    }
}
