<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Rewards
 */


namespace Amasty\Rewards\Block\Frontend;

use Amasty\Rewards\Api\CheckoutHighlightManagementInterface;
use Amasty\Rewards\Api\GuestHighlightManagementInterface;
use Amasty\Rewards\Helper\Data;
use Amasty\Rewards\Model\ArrayPathTrait;
use Amasty\Rewards\Model\Config as ConfigProvider;
use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;

class LayoutProcessor implements LayoutProcessorInterface
{
    use ArrayPathTrait;

    /**
     * @var Data
     */
    private $data;

    /**
     * @var CheckoutHighlightManagementInterface
     */
    private $highlightManagement;

    /**
     * @var GuestHighlightManagementInterface
     */
    private $guestHighlightManagement;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        Data $data,
        CheckoutHighlightManagementInterface $highlightManagement,
        GuestHighlightManagementInterface $guestHighlightManagement,
        ConfigProvider $configProvider
    ) {
        $this->data = $data;
        $this->highlightManagement = $highlightManagement;
        $this->guestHighlightManagement = $guestHighlightManagement;
        $this->configProvider = $configProvider;
    }

    /**
     * @param array $jsLayout
     *
     * @return array
     */
    public function process($jsLayout)
    {
        $highlightPath = 'components/checkout/./sidebar/./summary/./amasty-rewards-highlight';

        if (!$this->configProvider->isEnabled()) {
            $this->unsetArrayValueByPath(
                $jsLayout,
                'components/checkout/./steps/./billing-step/./payment/./afterMethods/./rewards'
            );
            $this->unsetArrayValueByPath(
                $jsLayout,
                $highlightPath
            );

            return $jsLayout;
        }

        $this->setToArrayByPath(
            $jsLayout,
            'components/checkout/./steps/./billing-step/./payment/./afterMethods/./rewards',
            $this->data->getRewardsData()
        );

        if ($this->highlightManagement->isVisible(CheckoutHighlightManagementInterface::CHECKOUT)) {
            $this->setToArrayByPath(
                $jsLayout,
                $highlightPath,
                $this->highlightManagement->getHighlightData()
            );
        } elseif ($this->guestHighlightManagement->isVisible(GuestHighlightManagementInterface::PAGE_CHECKOUT)) {
            $this->setToArrayByPath(
                $jsLayout,
                $highlightPath . '/component',
                'Amasty_Rewards/js/guest-highlight',
                false
            );
            $this->setToArrayByPath(
                $jsLayout,
                $highlightPath . '/highlight',
                $this->guestHighlightManagement
                    ->getHighlight(GuestHighlightManagementInterface::PAGE_CHECKOUT)
                    ->getData()
            );
        } else {
            $this->unsetArrayValueByPath($jsLayout, $highlightPath);
        }

        return $jsLayout;
    }
}
