<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MWishlist
 */


declare(strict_types=1);

namespace Amasty\MWishlist\Setup;

use Amasty\MWishlist\Setup\Operation\AddWishlistColumns;
use Amasty\MWishlist\Setup\Operation\UpdateCustomerKey;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * @var UpdateCustomerKey
     */
    private $updateCustomerKey;

    /**
     * @var AddWishlistColumns
     */
    private $addWishlistColumns;

    public function __construct(
        AddWishlistColumns $addWishlistColumns,
        UpdateCustomerKey $updateCustomerKey
    ) {
        $this->updateCustomerKey = $updateCustomerKey;
        $this->addWishlistColumns = $addWishlistColumns;
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $this->updateCustomerKey->execute($setup);
        $this->addWishlistColumns->execute($setup);

        $setup->endSetup();
    }
}
