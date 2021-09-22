<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ThankYouPage
 */


namespace Amasty\ThankYouPage\Plugin\InventoryCatalog\Model\ResourceModel;

use Amasty\ThankYouPage\Block\Onepage\Success\Types\Crosssell\Items;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\InventoryCatalog\Model\ResourceModel\AddStockDataToCollection;

class AddStockDataToCollectionPlugin
{
    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    public function __construct(DataPersistorInterface $dataPersistor)
    {
        $this->dataPersistor = $dataPersistor;
    }

    /**
     * @param AddStockDataToCollection $subject
     * @param Collection $collection
     * @param bool $isShowOutOfStock
     * @param int $stockId
     *
     * @return array
     */
    public function beforeExecute(
        AddStockDataToCollection $subject,
        Collection $collection,
        $isShowOutOfStock,
        $stockId
    ) {
        if ($this->dataPersistor->get(Items::AMASTY_THANKYOUPAGE_SHOW_OOS)) {
            $isShowOutOfStock = false;
            $this->dataPersistor->clear(Items::AMASTY_THANKYOUPAGE_SHOW_OOS);
        }

        return [$collection, $isShowOutOfStock, $stockId];
    }
}
