<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Plugin\Catalog\Block\Widget\RecentlyViewed;

use Amasty\Label\ViewModel\Label\Ui\Configuration;
use Magento\Catalog\Block\Widget\RecentlyViewed;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\View\Element\Template;

class AddHandlerScriptAfterBlockOutput implements ArgumentInterface
{
    const UI_HANDLER_TEMPLATE = 'Amasty_Label::ui_label.phtml';

    /**
     * @var Configuration
     */
    private $uiConfigurationProvider;

    public function __construct(
        Configuration $uiConfigurationProvider
    ) {
        $this->uiConfigurationProvider = $uiConfigurationProvider;
    }

    public function afterToHtml(RecentlyViewed $subject, string $output): string
    {
        $uiHandlerBlock = $subject->getLayout()->createBlock(
            Template::class,
            sprintf('amasty_label_ui_label_initialize_%s', uniqid()),
            ['data' => ['view_model' => $this->uiConfigurationProvider]]
        );
        $uiHandlerBlock->setTemplate(self::UI_HANDLER_TEMPLATE);

        return $output . $uiHandlerBlock->toHtml();
    }
}
