<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Setup\Patch\Data;

use Amasty\Label\Model\ResourceModel\Label;
use Amasty\Label\Setup\Model\DeployExamples;
use Amasty\Label\Setup\Model\DeployExamplesFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Zend_Db_Expr;

class DeployLabelExamples implements DataPatchInterface
{
    const EXAMPLES_FILE_NAME = '1.json';

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var DeployExamplesFactory
     */
    private $deployExamplesFactory;
    
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        DeployExamplesFactory $deployExamplesFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->deployExamplesFactory = $deployExamplesFactory;
    }

    public static function getDependencies(): array
    {
        return [
            SetUseInPromoConditionsForPrice::class,
            MoveDataToNewTableSchema::class
        ];
    }

    public function getAliases(): array
    {
        return [];
    }

    public function apply(): DeployLabelExamples
    {
        if ($this->isCanApply()) {
            $this->deployExamples();
        }

        return $this;
    }

    private function deployExamples(): void
    {
        /** @var DeployExamples $exampleDeployer **/
        $exampleDeployer = $this->deployExamplesFactory->create();
        $exampleDeployer->setFileName(self::EXAMPLES_FILE_NAME);
        $exampleDeployer->execute($this->moduleDataSetup);
    }

    private function isCanApply(): bool
    {
        $connection = $this->moduleDataSetup->getConnection();
        $select = $connection->select();
        $select->from(
            $this->moduleDataSetup->getTable(Label::TABLE_NAME),
            [new Zend_Db_Expr('COUNT(*)')]
        );

        return !$connection->fetchOne($select);
    }
}
