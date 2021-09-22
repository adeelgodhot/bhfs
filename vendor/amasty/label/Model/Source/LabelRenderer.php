<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


namespace Amasty\Label\Model\Source;

use Amasty\Label\Api\Data\LabelInterface;
use Amasty\Label\Model\ConfigProvider;
use Amasty\Label\Model\Label\Actions\IsLabelExists;
use Amasty\Label\Model\ResourceModel\Label\CollectionFactory;
use Magento\Backend\Block\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DB\Select;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

class LabelRenderer implements \Magento\Framework\Option\ArrayInterface, \Magento\Config\Model\Config\CommentInterface
{
    /**
     * @var CollectionFactory
     */
    private $labelCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var IsLabelExists
     */
    private $isLabelExists;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        CollectionFactory $labelCollectionFactory,
        StoreManagerInterface $storeManager,
        Context $context,
        RequestInterface $request,
        IsLabelExists $isLabelExists,
        ConfigProvider $configProvider
    ) {
        $this->labelCollectionFactory = $labelCollectionFactory;
        $this->storeManager = $storeManager;
        $this->context = $context;
        $this->request = $request;
        $this->isLabelExists = $isLabelExists;
        $this->configProvider = $configProvider;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $storeId = $this->request->getParam('store', $this->storeManager->getStore()->getId());
        $collection = $this->labelCollectionFactory->create()
            ->setOrder(LabelInterface::PRIORITY, Select::SQL_ASC);
        $collection->addStoreFilter([$storeId, Store::DEFAULT_STORE_ID]);
        $labels = [['value' => 0, 'label' => __('-- Please select --')]];

        foreach ($collection as $label) {
            $labels[] = [
                'value' => $label->getId(),
                'label' => $label->getName()
            ];
        }

        return $labels;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $labels = [];
        foreach ($this->toOptionArray() as $label) {
            $labels[$label['value']] = $label['label'];
        }

        return $labels;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param string $currentValue
     * @return string
     */
    public function getCommentText($currentValue = '')
    {
        $labelId = $this->configProvider->isOutOfStockLabelEnabled()
            ? (int) $this->configProvider->getDefaultOutOfStockLabelId()
            : 0;

        if ($this->isLabelExists->check($labelId)) {
            $url = $this->context->getUrlBuilder()->getUrl('amasty_label/label/edit', ['id' => $labelId]);
        } else {
            $url = $this->context->getUrlBuilder()->getUrl('amasty_label/label/new');
        }

        $comment = __(
            'Set \'Yes\' to show only \'Out of Stock\' label and hide all other active labels'
            . ' if the item is Out of Stock.'
            . ' Please click <a target="_blank" href="%1">here</a> to manage the \'Out of Stock\' label display.',
            $url
        );

        return $comment;
    }
}
