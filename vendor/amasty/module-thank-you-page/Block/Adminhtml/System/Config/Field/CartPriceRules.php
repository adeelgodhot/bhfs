<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ThankYouPage
 */

namespace Amasty\ThankYouPage\Block\Adminhtml\System\Config\Field;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Text;
use Magento\SalesRule\Api\RuleRepositoryInterface;

/**
 * Block for ordering sorting component in Admin System Configuration
 */
class CartPriceRules extends \Magento\Config\Block\System\Config\Form\Field
{

    /**
     * @var Text
     */
    private $textElement;

    /**
     * @var RuleRepositoryInterface
     */
    private $ruleRepository;

    public function __construct(
        Context $context,
        Text $textElement,
        RuleRepositoryInterface $ruleRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->textElement = $textElement;
        $this->ruleRepository = $ruleRepository;
    }

    /**
     * @param AbstractElement $element
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return parent::_getElementHtml($element)
            . $this->getFrontendElementHtml($element)
            . $this->getElementAfterHtml($element);
    }

    /**
     * @param AbstractElement $element
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getElementAfterHtml(AbstractElement $element)
    {
        $htmlId = $element->getHtmlId();
        $selectorOptions = \json_encode($this->_getSelectorOptions($element));

        return <<<HTML
        <script>
    require(["jquery", "collapsable", "mage/mage","mage/backend/suggest"],function ($) {
        var suggest = $('#$htmlId-suggest'),
            selectedContainer = $('#$htmlId-selected');
        
        selectedContainer.insertAfter(selectedContainer.parent('.value').find('.note'))
        
        suggest
            .mage('suggest', $selectorOptions)
            .on('suggestselect', function (e, ui) {
                if (ui.item.id) {
                    selectedContainer.show();
                    selectedContainer.find('span').text('#' + ui.item.id + ' - ' + ui.item.label);
                    suggest.val('');
                } else {
                    selectedContainer.hide();
                }
            })
            .on('blur', function() {
              suggest.val('')
            })
        
        selectedContainer.find('a').on('click', function() {
            selectedContainer.hide();
            $('#$htmlId').val('');
        })
    });
</script>
HTML;
    }

    /**
     * Get selector options
     *
     * @param AbstractElement $element
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function _getSelectorOptions(AbstractElement $element)
    {
        $ruleId = null;
        if ($rule = $this->getSelectedRule($element)) {
            $ruleId = $rule->getRuleId();
        }

        return [
            'source'            => $this->getUrl('thankyoupage/salesRule/suggest'),
            'valueField'        => '#' . $element->getHtmlId(),
            'minLength'         => 1,
            'currentlySelected' => $ruleId,
        ];
    }

    /**
     * @param AbstractElement $element
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getFrontendElementHtml(AbstractElement $element)
    {
        $id = $element->getHtmlId();
        $this->textElement->setForm($element->getForm())
            ->setId($id . '-suggest');
        $selectedText = __('Selected Rule: ');
        $ruleName = '';
        $display = 'none';

        if ($rule = $this->getSelectedRule($element)) {
            $ruleName = '#' . $rule->getRuleId() . ' - ' . $rule->getName();
            $display = 'block';
        }


        $selectedHtml = <<<HTML
<div id="$id-selected" style="display: $display">
    <div>
        <i>$selectedText</i><span>$ruleName</span>
        <a href="#" onclick="return false;">[x]</a>
    </div>
</div>
HTML;

        return $this->textElement->getElementHtml() . $selectedHtml;
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     *
     * @return \Magento\SalesRule\Api\Data\RuleInterface|null
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getSelectedRule(AbstractElement $element)
    {
        if ($selectedId = $element->getEscapedValue()) {
            $rule = $this->ruleRepository->getById($selectedId);
            if ($rule->getRuleId()) {
                return $rule;
            }
        }

        return null;
    }
}
