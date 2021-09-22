<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Ui\DataProvider\Label;

use Amasty\Label\Api\Data\LabelInterface;
use Amasty\Label\Model\Label;
use Amasty\Label\Model\LabelRegistry;
use Amasty\Label\Model\ResourceModel\Label\CollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Ui\DataProvider\Modifier\PoolInterface as UiDataModifiersPool;

class Form extends AbstractDataProvider
{
    /**
     * @var ?array
     */
    private $loadedData;

    /**
     * @var UiDataModifiersPool
     */
    private $uiDataModifiersPool;

    /**
     * @var LabelRegistry
     */
    private $labelRegistry;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        LabelRegistry $labelRegistry,
        CollectionFactory $collectionFactory,
        UiDataModifiersPool $uiDataModifiersPool,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->uiDataModifiersPool = $uiDataModifiersPool;
        $this->labelRegistry = $labelRegistry;

        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getData(): array
    {
        if ($this->loadedData === null) {
            $persistedData = $this->labelRegistry->registry(LabelRegistry::PERSISTED_DATA);

            if (!empty($persistedData)) {
                $this->loadedData[$persistedData[LabelInterface::LABEL_ID]] = $persistedData->getData();
            } else {
                /** @var Label $label **/
                $label = $this->getCollection()->getFirstItem();
                $this->loadedData[$label->getId()] = $label->getData();

                foreach ($this->uiDataModifiersPool->getModifiersInstances() as $modifier) {
                    $this->loadedData = $modifier->modifyData($this->loadedData);
                }
            }
        }

        return $this->loadedData;
    }

    public function getMeta(): array
    {
        $meta = parent::getMeta();

        foreach ($this->uiDataModifiersPool->getModifiersInstances() as $modifier) {
            $meta = $modifier->modifyMeta($meta);
        }

        return $meta;
    }
}
