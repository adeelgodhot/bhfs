<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringStripe
 */


declare(strict_types=1);

namespace Amasty\RecurringStripe\Setup;

use Amasty\RecurringStripe\Model\ResourceModel\StripeProduct;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;

class Uninstall implements UninstallInterface
{
    /**
     * @inheritDoc
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->removeTables($setup);
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function removeTables(SchemaSetupInterface $setup)
    {
        $setup->startSetup()->getConnection()->delete(StripeProduct::TABLE_NAME);
    }
}
