<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ThankYouPage
 */


namespace Amasty\ThankYouPage\Plugin\CatalogInventory\Model\ResourceModel\Stock;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Status;
use Magento\Framework\App\Request\DataPersistorInterface;
use Amasty\ThankYouPage\Block\Onepage\Success\Types\Crosssell\Items;

class StatusPlugin
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
     * @param Status $subject
     * @param Collection $collection
     * @param bool $isShowOutOfStock
     *
     * @return array
     */
    public function beforeAddStockDataToCollection(
        Status $subject,
        Collection $collection,
        $isShowOutOfStock
    ) {
        if ($this->dataPersistor->get(Items::AMASTY_THANKYOUPAGE_SHOW_OOS)) {
            $isShowOutOfStock = false;
            $this->dataPersistor->clear(Items::AMASTY_THANKYOUPAGE_SHOW_OOS);
        }

        return [$collection, $isShowOutOfStock];
    }
}
