<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */


namespace Amasty\Followup\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var \Amasty\Followup\Helper\Data
     */
    protected $helper;

    /**
     * @param \Amasty\Followup\Helper\Data $helper
     */
    public function __construct(
        \Amasty\Followup\Helper\Data $helper
    ) {

        $this->helper = $helper;
    }

    /**
     * {@inheritdoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $helper = $this->helper;

        $helper->createTemplate('amfollowup_order_new', 'Amasty Follow Up Email: Order Created');
        $helper->createTemplate('amfollowup_order_ship', 'Amasty Follow Up Email: Order Shipped');
        $helper->createTemplate('amfollowup_order_invoice', 'Amasty Follow Up Email: Order Invoiced');
        $helper->createTemplate('amfollowup_order_complete', 'Amasty Follow Up Email: Order Completed');
        $helper->createTemplate('amfollowup_order_cancel', 'Amasty Follow Up Email: Order Cancelled');
        $helper->createTemplate('amfollowup_customer_group', 'Amasty Follow Up Email: Customer Changed Group');
        $helper->createTemplate('amfollowup_customer_birthday', 'Amasty Follow Up Email: Customer Birthday');
        $helper->createTemplate('amfollowup_customer_new', 'Amasty Follow Up Email: Customer Registration');
        $helper->createTemplate(
            'amfollowup_customer_subscription',
            'Amasty Follow Up Email: Customer Subscribed to Newsletter'
        );
        $helper->createTemplate(
            'amfollowup_customer_activity',
            'Amasty Follow Up Email: Customer No Activity'
        );

        $helper->createTemplate(
            'amfollowup_customer_date',
            'Amasty Follow Up Email: Merry Christmas'
        );

        $helper->createTemplate(
            'amfollowup_customer_wishlist',
            'Amasty Follow Up Email: Customer Wish List Product Added'
        );

        $helper->createTemplate(
            'amfollowup_customer_wishlist_shared',
            'Amasty Follow Up Email: Customer Wish List Shared '
        );

        $helper->createTemplate(
            'amfollowup_customer_wishlist_sale',
            'Amasty Follow Up Email: Customer Wish List on Sale'
        );

        $helper->createTemplate(
            'amfollowup_customer_wishlist_back_instock',
            'Amasty Follow Up Email: Customer Wish List Product Back In Stock'
        );

        $setup->endSetup();

    }
}
