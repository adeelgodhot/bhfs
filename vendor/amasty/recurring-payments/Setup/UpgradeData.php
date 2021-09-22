<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


namespace Amasty\RecurringPayments\Setup;

use Amasty\Base\Helper\Deploy;
use Amasty\RecurringPayments\Setup\Operation\AddPlansAttributeToProduct;
use Amasty\RecurringPayments\Setup\Operation\CreatePlansAndAssign;
use Amasty\RecurringPayments\Setup\Operation\FillBaseGrandTotalWithDiscount;
use Amasty\RecurringPayments\Setup\Operation\FillCustomerEmailField;
use Amasty\RecurringPayments\Setup\Operation\RemoveOldProductAttributes;
use Amasty\RecurringPayments\Setup\Operation\UpdateStartDateField;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var UpdateStartDateField
     */
    private $updateStartDateField;

    /**
     * @var FillCustomerEmailField
     */
    private $fillCustomerEmailField;

    /**
     * @var Deploy
     */
    private $pubDeployer;

    /**
     * @var AddPlansAttributeToProduct
     */
    private $addPlansAttributeToProduct;

    /**
     * @var RemoveOldProductAttributes
     */
    private $removeOldProductAttributes;

    /**
     * @var FillBaseGrandTotalWithDiscount
     */
    private $fillBaseGrandTotalWithDiscount;

    /**
     * @var CreatePlansAndAssign
     */
    private $createPlansAndAssign;

    public function __construct(
        UpdateStartDateField $updateStartDateField,
        FillCustomerEmailField $fillCustomerEmailField,
        Deploy $pubDeployer,
        AddPlansAttributeToProduct $addPlansAttributeToProduct,
        RemoveOldProductAttributes $removeOldProductAttributes,
        FillBaseGrandTotalWithDiscount $fillBaseGrandTotalWithDiscount,
        CreatePlansAndAssign $createPlansAndAssign
    ) {
        $this->updateStartDateField = $updateStartDateField;
        $this->fillCustomerEmailField = $fillCustomerEmailField;
        $this->pubDeployer = $pubDeployer;
        $this->addPlansAttributeToProduct = $addPlansAttributeToProduct;
        $this->removeOldProductAttributes = $removeOldProductAttributes;
        $this->fillBaseGrandTotalWithDiscount = $fillBaseGrandTotalWithDiscount;
        $this->createPlansAndAssign = $createPlansAndAssign;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.5.0', '<')) {
            $this->updateStartDateField->execute($setup);
            $this->fillCustomerEmailField->execute($setup);
            $this->pubDeployer->deployFolder(__DIR__.'/../pub');
        }

        if (version_compare($context->getVersion(), '1.6.0', '<')) {
            $this->addPlansAttributeToProduct->execute($setup);
            $this->fillBaseGrandTotalWithDiscount->execute($setup);
            $this->createPlansAndAssign->execute($setup);
            $this->removeOldProductAttributes->execute($setup);
        }
    }
}
