<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


namespace Amasty\Label\Plugin\Catalog\Product;

use Amasty\Label\Model\AbstractLabels;
use Amasty\Label\Model\ResourceModel\Label\Collection;

class ListProduct
{
    /**
     * @var \Amasty\Label\Model\LabelViewer
     */
    private $helper;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    public function __construct(
        \Amasty\Label\Model\LabelViewer $helper,
        \Magento\Framework\Registry $registry
    ) {
        $this->helper = $helper;
        $this->registry = $registry;
    }

    /**
     * @param  $subject
     * @param $result
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterToHtml(
        $subject,
        $result
    ) {
        if (!$this->registry->registry('amlabel_category_observer') && !$subject->getIsAmLabelObserved()) {
            $products = $subject->getLoadedProductCollection();
            if (!$products) {
                $products = $subject->getProductCollection();
            }

            if ($products) {
                foreach ($products as $product) {
                    $result .= $this->helper->renderProductLabel(
                        $product,
                        Collection::MODE_LIST,
                        true
                    );
                }
                $subject->setIsAmLabelObserved(true);
            }
        }

        return $result;
    }
}
