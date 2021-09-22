<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Ui\Component\Form\Label\Buttons;

use Amasty\Label\ViewModel\Adminhtml\Labels\Edit\GetCurrentLabelData;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class Duplicate implements ButtonProviderInterface
{
    /**
     * @var GetCurrentLabelData
     */
    private $getCurrentLabelData;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    public function __construct(
        GetCurrentLabelData $getCurrentLabelData,
        UrlInterface $urlBuilder
    ) {
        $this->getCurrentLabelData = $getCurrentLabelData;
        $this->urlBuilder = $urlBuilder;
    }

    public function getButtonData()
    {
        $data = [];

        if (!$this->getCurrentLabelData->isNewLabel()) {
            $data = [
                'label' => __('Duplicate'),
                'class' => 'save',
                'on_click' => 'setLocation(\'' . $this->getDuplicateUrl() . '\')',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => ['event' => 'UpdateEdit', 'target' => '#edit_form'],
                    ],
                ],
                'sort_order' => 111
            ];
        }

        return $data;
    }

    private function getDuplicateUrl(): string
    {
        return $this->urlBuilder->getUrl(
            '*/*/duplicate',
            ['_current' => true, 'back' => null, Delete::ID => $this->getCurrentLabelData->getLabelId()]
        );
    }
}
