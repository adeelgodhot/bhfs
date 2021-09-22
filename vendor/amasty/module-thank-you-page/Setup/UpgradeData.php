<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ThankYouPage
 */


declare(strict_types=1);

namespace Amasty\ThankYouPage\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Amasty\ThankYouPage\Setup\Operation\EnableMobileViewConfig;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var EnableMobileViewConfig
     */
    private $enableMobileViewConfig;

    public function __construct(EnableMobileViewConfig $enableMobileViewConfig)
    {
        $this->enableMobileViewConfig = $enableMobileViewConfig;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.1.8', '<') && $context->getVersion() != null) {
            $this->enableMobileViewConfig->execute();
        }

        $setup->endSetup();
    }
}
