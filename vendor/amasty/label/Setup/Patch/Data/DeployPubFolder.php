<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Setup\Patch\Data;

use Amasty\Base\Helper\Deploy;
use Amasty\Label\Model\ResourceModel\Label;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Validation\ValidationException;
use Zend_Db_Expr;

class DeployPubFolder implements DataPatchInterface
{
    const STATIC_FILES_FOLDER = 'data/pub';

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var Deploy
     */
    private $pubDeploy;

    /**
     * @var ComponentRegistrar
     */
    private $componentRegistrar;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        Deploy $pubDeploy,
        ComponentRegistrar $componentRegistrar
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->pubDeploy = $pubDeploy;
        $this->componentRegistrar = $componentRegistrar;
    }

    public static function getDependencies(): array
    {
        return [
            SetUseInPromoConditionsForPrice::class,
            MoveDataToNewTableSchema::class,
            DeployLabelExamples::class
        ];
    }

    public function getAliases(): array
    {
        return [];
    }

    public function apply(): self
    {
        $this->deployModuleFiles();

        return $this;
    }

    private function deployModuleFiles(): void
    {
        $moduleDir = $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, 'Amasty_Label');

        try {
            $this->pubDeploy->deployFolder($moduleDir . DIRECTORY_SEPARATOR . self::STATIC_FILES_FOLDER);
        } catch (ValidationException $e) {
            null; //skip this step
        }
    }
}
