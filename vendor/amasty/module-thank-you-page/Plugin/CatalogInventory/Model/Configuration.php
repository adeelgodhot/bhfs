<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ThankYouPage
 */

namespace Amasty\ThankYouPage\Plugin\CatalogInventory\Model;

use Magento\CatalogInventory\Model\Configuration as ConfigurationInventory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Amasty\ThankYouPage\Model\Config\Types\Crosssell;
use Amasty\ThankYouPage\Block\Onepage\Success\Types\Crosssell\Items;

class Configuration
{
    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var Crosssell
     */
    private $crosssellConfig;

    public function __construct(
        DataPersistorInterface $dataPersistor,
        Crosssell $crosssellConfig
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->crosssellConfig = $crosssellConfig;
        $this->crosssellConfig->setGroupPrefix('block_' . Crosssell::BLOCK_CONFIG_NAME);
    }

    /**
     * Rewrite to show Out Of Stock products in ThankYouPage Cross-Sell blocks if enabled in settings
     *
     * @param ConfigurationInventory $configuration
     * @param bool $resultCrosssell
     *
     * @return string
     */
    public function afterIsShowOutOfStock(ConfigurationInventory $configuration, $result)
    {
        if ($this->dataPersistor->get(Items::AMASTY_THANKYOUPAGE_SHOW_OOS)
            || $this->crosssellConfig->isShowOutOfStock()) {
            return true;
        }

        return $result;
    }
}
