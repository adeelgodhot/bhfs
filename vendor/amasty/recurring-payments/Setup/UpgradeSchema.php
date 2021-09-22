<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


namespace Amasty\RecurringPayments\Setup;

use Amasty\RecurringPayments\Setup\Operation\AddCountDiscountCyclesField;
use Amasty\RecurringPayments\Setup\Operation\AddCustomerEmailField;
use Amasty\RecurringPayments\Setup\Operation\AddDiscountFieldToSubscription;
use Amasty\RecurringPayments\Setup\Operation\AddStartEndDateFields;
use Amasty\RecurringPayments\Setup\Operation\AddTimezoneField;
use Amasty\RecurringPayments\Setup\Operation\CreatePlansTable;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var Operation\CreateSubscriptionTable
     */
    private $createSubscriptionTable;

    /**
     * @var Operation\CreateScheduleTable
     */
    private $createScheduleTable;

    /**
     * @var Operation\AddIntervalAndStatusFieldsToSubscriptionTable
     */
    private $addIntervalAndStatusFieldsToSubscriptionTable;

    /**
     * @var Operation\AddSubscriptionIdToTransactionTable
     */
    private $addSubscriptionIdToTransactionTable;

    /**
     * @var AddStartEndDateFields
     */
    private $addStartEndDateFields;

    /**
     * @var AddCountDiscountCyclesField
     */
    private $addCountDiscountCyclesField;

    /**
     * @var AddTimezoneField
     */
    private $addTimezoneField;

    /**
     * @var AddCustomerEmailField
     */
    private $addCustomerEmailField;

    /**
     * @var CreatePlansTable
     */
    private $createPlansTable;

    /**
     * @var AddDiscountFieldToSubscription
     */
    private $addDiscountFieldToSubscription;

    public function __construct(
        Filesystem $filesystem,
        Operation\CreateSubscriptionTable $createSubscriptionTable,
        Operation\CreateScheduleTable $createScheduleTable,
        Operation\AddIntervalAndStatusFieldsToSubscriptionTable $addIntervalAndStatusFieldsToSubscriptionTable,
        Operation\AddSubscriptionIdToTransactionTable $addSubscriptionIdToTransactionTable,
        AddStartEndDateFields $addStartEndDateFields,
        AddCountDiscountCyclesField $addCountDiscountCyclesField,
        AddTimezoneField $addTimezoneField,
        AddCustomerEmailField $addCustomerEmailField,
        CreatePlansTable $createPlansTable,
        AddDiscountFieldToSubscription $addDiscountFieldToSubscription
    ) {
        $this->filesystem = $filesystem;
        $this->createSubscriptionTable = $createSubscriptionTable;
        $this->createScheduleTable = $createScheduleTable;
        $this->addIntervalAndStatusFieldsToSubscriptionTable = $addIntervalAndStatusFieldsToSubscriptionTable;
        $this->addSubscriptionIdToTransactionTable = $addSubscriptionIdToTransactionTable;
        $this->addStartEndDateFields = $addStartEndDateFields;
        $this->addCountDiscountCyclesField = $addCountDiscountCyclesField;
        $this->addTimezoneField = $addTimezoneField;
        $this->addCustomerEmailField = $addCustomerEmailField;
        $this->createPlansTable = $createPlansTable;
        $this->addDiscountFieldToSubscription = $addDiscountFieldToSubscription;
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if ($context->getVersion() && version_compare($context->getVersion(), '1.1.0', '<')) {
            try {
                $this->validateDirectory();
            } catch (\RuntimeException $e) {
                throw $e;
            } catch (\Exception $e) {
                null;
            }
        }

        if (version_compare($context->getVersion(), '1.1.1', '<')) {
            $this->createSubscriptionTable->execute($setup);
        }

        if (version_compare($context->getVersion(), '1.4.0', '<')) {
            $this->createScheduleTable->execute($setup);
            $this->addIntervalAndStatusFieldsToSubscriptionTable->execute($setup);
            $this->addSubscriptionIdToTransactionTable->execute($setup);
        }

        if (version_compare($context->getVersion(), '1.5.0', '<')) {
            $this->addStartEndDateFields->execute($setup);
            $this->addCountDiscountCyclesField->execute($setup);
            $this->addTimezoneField->execute($setup);
            $this->addCustomerEmailField->execute($setup);
        }

        if (version_compare($context->getVersion(), '1.6.0', '<')) {
            $this->createPlansTable->execute($setup);
            $this->addDiscountFieldToSubscription->execute($setup);
        }
    }

    /**
     * validate old directory existing
     * @throws \RuntimeException
     */
    private function validateDirectory()
    {
        $directory = $this->filesystem->getDirectoryRead(DirectoryList::APP);
        if ($directory->isExist('code/Amasty/RecurringPayments/Controller/Stripe/Index.php')) {
            $message = "WARNING: This update requires removing folder app/code/Amasty/RecurringPayments.\n"
                . "Remove this folder and unpack new version of package into app/code/Amasty/RecurringPayments.\n"
                . "Run `php bin/magento setup:upgrade` again";
            throw new \RuntimeException($message);
        }
    }
}
