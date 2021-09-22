<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ThankYouPage
 */


declare(strict_types=1);

namespace Amasty\ThankYouPage\Setup\Operation;

use Magento\Config\Model\ResourceModel\ConfigFactory;

class EnableMobileViewConfig
{
    /**
     * @var ConfigFactory
     */
    private $configFactory;

    public function __construct(ConfigFactory $configFactory)
    {
        $this->configFactory = $configFactory;
    }

    /**
     * Set in config default value advanced_layout_management/mobile_view = 0 if module installed
     */
    public function execute()
    {
        /** @var \Magento\Config\Model\ResourceModel\Config $configResource */
        $configResource = $this->configFactory->create();
        $configResource->saveConfig('amasty_thank_you_page/advanced_layout_management/mobile_view', 0);
    }
}
