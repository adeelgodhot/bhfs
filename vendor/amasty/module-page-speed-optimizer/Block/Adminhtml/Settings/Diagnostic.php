<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */

declare(strict_types=1);

namespace Amasty\PageSpeedOptimizer\Block\Adminhtml\Settings;

use Magento\Backend\Block\Template;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Locale\Resolver as LocaleResolver;
use Magento\Framework\Url;

class Diagnostic extends Field
{
    private $urlBuilder;

    /**
     * @var string
     */
    protected $_template = 'Amasty_PageSpeedOptimizer::diagnostic.phtml';

    /**
     * @var LocaleResolver
     */
    private $localeResolver;

    public function __construct(
        LocaleResolver $localeResolver,
        Template\Context $context,
        Url $urlBuilder,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->urlBuilder = $urlBuilder;
        $this->localeResolver = $localeResolver;
    }

    public function getFrontendUrl(): string
    {
        if ($storeId = $this->getRequest()->getParam('store')) {
            $url = $this->urlBuilder->getUrl(null, ['_scope' => $storeId]);
        } else {
            $url = parent::getBaseUrl();
        }

        return $url;
    }

    public function getLocale(): string
    {
        return $this->localeResolver->getLocale();
    }

    protected function _getElementHtml(AbstractElement $element): string
    {
        return $this->toHtml();
    }
}
