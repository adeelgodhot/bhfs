<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ExitPopup
 */


namespace Amasty\ExitPopup\Block\Adminhtml\System\Config\Field;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Text;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\SalesRule\Api\RuleRepositoryInterface;
use Magento\Config\Block\System\Config\Form\Field;
use Amasty\ExitPopup\Model\ConfigProvider;
use Magento\Catalog\Model\Product;
use Magento\SalesRule\Model\Rule;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class AbstractHtmlElement extends Field
{
    const PROMO_SETTINGS_PATH = 'amasty_exit_popup_promo_settings_';
    const RULE_ENTITY = 'Rule';
    const PRODUCT_ENTITY = 'Product';

    /**
     * @var Text
     */
    private $textElement;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var RuleRepositoryInterface
     */
    private $ruleRepository;

    /**
     * @var string
     */
    private $entityName;

    public function __construct(
        Context $context,
        Text $textElement,
        ProductRepositoryInterface $productRepository,
        RuleRepositoryInterface $ruleRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->textElement = $textElement;
        $this->productRepository = $productRepository;
        $this->ruleRepository = $ruleRepository;
    }

    /**
     * @inheritdoc
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $element->setDisabled(false);

        return parent::_getElementHtml($element)
            . $this->getFrontendElementHtml($element)
            . $this->getElementAfterHtml($element);
    }

    /**
     * @param AbstractElement $element
     *
     * @return string
     */
    private function getElementAfterHtml(AbstractElement $element)
    {
        $selectorOptions = \Zend_Json::encode($this->getSelectorOptions($element, $this->entityName));

        return '<script type="text/x-magento-init"> 
            {
                "#'. $element->getHtmlId() . '": {
                    "Amasty_ExitPopup/js/note": {
                        "htmlId":"' . $element->getHtmlId() . '",
                        "selectorOptions":' . $selectorOptions . '
                    }
                }
            }
        </script>';
    }

    /**
     * @param AbstractElement $element
     *
     * @return string
     */
    private function getFrontendElementHtml(AbstractElement $element)
    {
        $htmlId = $element->getHtmlId();
        $this->entityName = '';
        $label = '';

        if ($htmlId === self::PROMO_SETTINGS_PATH . ConfigProvider::RULE_ID_FIELD) {
            $this->entityName = self::RULE_ENTITY;
        } elseif ($htmlId === self::PROMO_SETTINGS_PATH . ConfigProvider::PRODUCT_ID) {
            $this->entityName = self::PRODUCT_ENTITY;
        }

        $this->textElement->setForm($element->getForm())
            ->setId($htmlId . '-suggest');
        $selectedText = __('Selected ' . $this->entityName . ': ');
        $display = 'none';

        /** @var Rule|Product $entity */
        if ($entity = $this->getSelectedEntity($element, $this->entityName)) {
            if ($this->entityName === self::RULE_ENTITY) {
                $label = '#' . $entity->getRuleId() . ' - ' . $entity->getName();
            } elseif ($this->entityName === self::PRODUCT_ENTITY) {
                $label = $entity->getSku() . ' - ' . $entity->getName();
            }
            $display = 'block';
        }


        $selectedHtml = <<<HTML
<div id="$htmlId-selected" style="display: $display">
    <div>
        <i>$selectedText</i><span>$label</span>
        <a href="#" onclick="return false;">[x]</a>
    </div>
</div>
HTML;

        return $this->textElement->getElementHtml() . $selectedHtml;
    }

    /**
     * @param AbstractElement $element
     * @param string $entityName
     *
     * @return Product|Rule|null
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function getSelectedEntity(AbstractElement $element, $entityName)
    {
        $entity = '';
        if ($selectedId = $element->getEscapedValue()) {
            if ($entityName === self::RULE_ENTITY) {
                /** @var Product $entity */
                $entity = $this->ruleRepository->getById($selectedId);
            } elseif ($entityName === self::PRODUCT_ENTITY) {
                /** @var Rule $entity */
                $entity = $this->productRepository->getById($selectedId);
            }

            if ($entity) {
                return $entity;
            }
        }

        return null;
    }

    /**
     * @param AbstractElement $element
     * @param string $entityName
     *
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function getSelectorOptions(AbstractElement $element, $entityName)
    {
        $entityId = null;

        /** @var Rule|Product $entity */
        if ($entity = $this->getSelectedEntity($element, $entityName)) {
            if ($entityName === self::RULE_ENTITY) {
                $entityId = $entity->getRuleId();
            } elseif ($entityName === self::PRODUCT_ENTITY) {
                $entityId = $entity->getId();
            }
        }

        return [
            'source'            => $this->getUrl($this->getSuggestUrl($entityName)),
            'valueField'        => '#' . $element->getHtmlId(),
            'minLength'         => 1,
            'currentlySelected' => $entityId,
        ];
    }

    /**
     * @param string $entityName
     *
     * @return string
     */
    private function getSuggestUrl($entityName)
    {
        $url = '';

        if ($entityName === self::RULE_ENTITY) {
            $url = 'exitpopup/salesRule/suggest';
        } elseif ($entityName === self::PRODUCT_ENTITY) {
            $url = 'exitpopup/downloadableProduct/suggest';
        }

        return $url;
    }
}
