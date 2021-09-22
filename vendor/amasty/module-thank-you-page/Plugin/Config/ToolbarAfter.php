<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ThankYouPage
 */


namespace Amasty\ThankYouPage\Plugin\Config;

use Amasty\ThankYouPage\Block\Adminhtml\System\Config\Tooltip;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Widget\Button;
use Magento\Config\Model\Config\Structure;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

class ToolbarAfter
{
    const PREVIEW_URL = 'thankyoupage/preview/view';
    const SECTION_ID = 'amasty_thank_you_page';

    /**
     * @var Structure
     */
    private $configStructure;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    public function __construct(
        Structure $configStructure,
        RequestInterface $request,
        StoreManagerInterface $storeManager,
        UrlInterface $urlBuilder
    ) {
        $this->configStructure = $configStructure;
        $this->request = $request;
        $this->storeManager = $storeManager;
        $this->urlBuilder = $urlBuilder;
    }

    public function afterGetToolbar(
        Template $subject,
        $result
    ) {
        $section = $this->configStructure->getElement($this->request->getParam('section'));
        $storeId = $this->request->getParam('store');
        $store = $this->storeManager->getStore($storeId);

        if ($section && $section->getId() === self::SECTION_ID) {
            $routeParams = [
                '_query' => [
                    '___store' => $store->getCode()
                ]
            ];

            $result->addChild(
                'preview_button',
                Button::class,
                [
                    'id' => 'amasty_success_preview',
                    'label' => __('Preview'),
                    'class' => 'amtypage-preview-button preview',
                    'onclick' => sprintf(
                        "window.open('%s');",
                        $this->urlBuilder->getUrl(self::PREVIEW_URL, $routeParams)
                    ),
                    'data_attribute' => [
                        'mage-init' => ['button' => ['event' => 'preview', 'target' => '_blank']],
                    ]
                ]
            );

            $result->addChild(
                'preview_tooltip',
                Tooltip::class,
                [
                    'id' => 'amasty_preview_tooltip'
                ]
            );
        }

        return $result;
    }
}
