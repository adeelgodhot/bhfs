<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */


namespace Amasty\Followup\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Setup\ExternalFKSetup;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\SalesRule\Api\Data\RuleInterface;

class Recurring implements InstallSchemaInterface
{
    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var ExternalFKSetup
     */
    private $externalFKSetup;

    public function __construct(
        MetadataPool $metadataPool,
        ExternalFKSetup $externalFKSetup
    ) {
        $this->metadataPool = $metadataPool;
        $this->externalFKSetup = $externalFKSetup;
    }

    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $this->addExternalForeignKeys($setup);
        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $installer
     *
     * @return void
     * @throws \Exception
     */
    protected function addExternalForeignKeys(SchemaSetupInterface $installer)
    {
        $metadata = $this->metadataPool->getMetadata(RuleInterface::class);
        $this->externalFKSetup->install(
            $installer,
            $metadata->getEntityTable(),
            $metadata->getIdentifierField(),
            'amasty_amfollowup_history',
            'sales_rule_id'
        );
    }
}

