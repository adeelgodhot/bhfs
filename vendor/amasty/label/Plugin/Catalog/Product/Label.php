<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


namespace Amasty\Label\Plugin\Catalog\Product;

use Amasty\Label\Model\AbstractLabels;

class Label
{
    const IGNORING_MODULE_NAMES = [
        'checkout',
        'amasty_quote'
    ];

    /**
     * @var \Amasty\Label\Model\LabelViewer
     */
    private $helper;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    public function __construct(
        \Amasty\Label\Model\LabelViewer $helper,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Registry $registry
    ) {
        $this->helper = $helper;
        $this->request = $request;
        $this->registry = $registry;
    }

    /**
     * @param \Magento\Catalog\Block\Product\Image $subject
     * @param $result
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterToHtml(
        \Magento\Catalog\Block\Product\Image $subject,
        $result
    ) {
        $product = $subject->getProduct();
        $moduleName = $this->request->getModuleName();
        if ($product && !in_array($moduleName, static::IGNORING_MODULE_NAMES)) {
            $result .= $this->helper->renderProductLabel($product);
            $this->registry->register('amlabel_category_observer', true, true);
        }

        return $result;
    }
}
