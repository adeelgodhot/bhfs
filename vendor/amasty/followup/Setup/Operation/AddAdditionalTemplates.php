<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */


namespace Amasty\Followup\Setup\Operation;

use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class AddAdditionalTemplates
{
    /**
     * @var \Amasty\Followup\Helper\Data
     */
    private $helper;

    /**
     * @param \Amasty\Followup\Helper\Data $helper
     */
    public function __construct(
        \Amasty\Followup\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @return $this
     */
    public function execute(ModuleDataSetupInterface $setup)
    {
        $helper = $this->helper;

        $helper->createTemplate(
            'amfollowup_order_new_modern', 'Amasty Follow Up Email: Order Created modern'
        );
        $helper->createTemplate(
            'amfollowup_order_new_winter_modern', 'Amasty Follow Up Email: Order Created modern winter theme'
        );
        $helper->createTemplate(
            'amfollowup_order_new_autumn_modern', 'Amasty Follow Up Email: Order Created modern autumn theme'
        );
        $helper->createTemplate(
            'amfollowup_order_new_summer_modern', 'Amasty Follow Up Email: Order Created modern summer theme'
        );
        $helper->createTemplate(
            'amfollowup_order_new_spring_modern', 'Amasty Follow Up Email: Order Created modern spring theme'
        );
        $helper->createTemplate(
            'amfollowup_order_new_christmas_1_modern', 'Amasty Follow Up Email: Order Created modern Christmas theme I'
        );
        $helper->createTemplate(
            'amfollowup_order_new_christmas_2_modern', 'Amasty Follow Up Email: Order Created modern Christmas theme II'
        );

        $helper->createTemplate('amfollowup_order_ship_modern', 'Amasty Follow Up Email: Order Shipped modern');
        $helper->createTemplate(
            'amfollowup_order_ship_winter_modern', 'Amasty Follow Up Email: Order Shipped modern winter theme'
        );
        $helper->createTemplate(
            'amfollowup_order_ship_autumn_modern', 'Amasty Follow Up Email: Order Shipped modern autumn theme'
        );
        $helper->createTemplate(
            'amfollowup_order_ship_summer_modern', 'Amasty Follow Up Email: Order Shipped modern summer theme'
        );
        $helper->createTemplate(
            'amfollowup_order_ship_spring_modern', 'Amasty Follow Up Email: Order Shipped modern spring theme'
        );
        $helper->createTemplate(
            'amfollowup_order_ship_christmas_1_modern', 'Amasty Follow Up Email: Order Shipped modern Christmas theme I'
        );
        $helper->createTemplate(
            'amfollowup_order_ship_christmas_2_modern', 'Amasty Follow Up Email: Order Shipped modern Christmas theme II'
        );

        $helper->createTemplate('amfollowup_order_invoice_modern', 'Amasty Follow Up Email: Order Invoiced modern');
        $helper->createTemplate(
            'amfollowup_order_invoice_winter_modern', 'Amasty Follow Up Email: Order Invoiced modern winter theme'
        );
        $helper->createTemplate(
            'amfollowup_order_invoice_autumn_modern', 'Amasty Follow Up Email: Order Invoiced modern autumn theme'
        );
        $helper->createTemplate(
            'amfollowup_order_invoice_summer_modern', 'Amasty Follow Up Email: Order Invoiced modern summer theme'
        );
        $helper->createTemplate(
            'amfollowup_order_invoice_spring_modern', 'Amasty Follow Up Email: Order Invoiced modern spring theme'
        );
        $helper->createTemplate(
            'amfollowup_order_invoice_christmas_1_modern',
            'Amasty Follow Up Email: Order Invoiced modern Christmas theme I'
        );
        $helper->createTemplate(
            'amfollowup_order_invoice_christmas_2_modern',
            'Amasty Follow Up Email: Order Invoiced modern Christmas theme II'
        );

        $helper->createTemplate('amfollowup_order_complete_modern', 'Amasty Follow Up Email: Order Completed modern');
        $helper->createTemplate(
            'amfollowup_order_complete_winter_modern',
            'Amasty Follow Up Email: Order Completed modern winter theme'
        );
        $helper->createTemplate(
            'amfollowup_order_complete_autumn_modern',
            'Amasty Follow Up Email: Order Completed modern autumn theme'
        );
        $helper->createTemplate(
            'amfollowup_order_complete_summer_modern',
            'Amasty Follow Up Email: Order Completed modern summer theme'
        );
        $helper->createTemplate(
            'amfollowup_order_complete_spring_modern',
            'Amasty Follow Up Email: Order Completed modern spring theme'
        );
        $helper->createTemplate(
            'amfollowup_order_complete_christmas_1_modern',
            'Amasty Follow Up Email: Order Completed modern Christmas theme I'
        );
        $helper->createTemplate(
            'amfollowup_order_complete_christmas_2_modern',
            'Amasty Follow Up Email: Order Completed modern Christmas theme II'
        );

        $helper->createTemplate('amfollowup_order_cancel_modern', 'Amasty Follow Up Email: Order Cancelled modern');
        $helper->createTemplate(
            'amfollowup_order_cancel_winter_modern',
            'Amasty Follow Up Email: Order Cancelled modern winter theme'
        );
        $helper->createTemplate(
            'amfollowup_order_cancel_autumn_modern',
            'Amasty Follow Up Email: Order Cancelled modern autumn theme'
        );
        $helper->createTemplate(
            'amfollowup_order_cancel_summer_modern',
            'Amasty Follow Up Email: Order Cancelled modern summer theme'
        );
        $helper->createTemplate(
            'amfollowup_order_cancel_spring_modern',
            'Amasty Follow Up Email: Order Cancelled modern spring theme'
        );
        $helper->createTemplate(
            'amfollowup_order_cancel_christmas_1_modern',
            'Amasty Follow Up Email: Order Cancelled modern Christmas theme I'
        );
        $helper->createTemplate(
            'amfollowup_order_cancel_christmas_2_modern',
            'Amasty Follow Up Email: Order Cancelled modern Christmas theme II'
        );

        $helper->createTemplate(
            'amfollowup_customer_date_winter_modern',
            'Amasty Follow Up Email: Merry Christmas modern winter theme'
        );
        $helper->createTemplate(
            'amfollowup_customer_date_autumn_modern',
            'Amasty Follow Up Email: Merry Christmas modern autumn theme'
        );
        $helper->createTemplate(
            'amfollowup_customer_date_summer_modern',
            'Amasty Follow Up Email: Merry Christmas modern summer theme'
        );
        $helper->createTemplate(
            'amfollowup_customer_date_spring_modern',
            'Amasty Follow Up Email: Merry Christmas modern spring theme'
        );
        $helper->createTemplate(
            'amfollowup_customer_date_christmas_1_modern',
            'Amasty Follow Up Email: Merry Christmas modern Christmas theme I'
        );
        $helper->createTemplate(
            'amfollowup_customer_date_christmas_2_modern',
            'Amasty Follow Up Email: Merry Christmas modern Christmas theme II'
        );

        $helper->createTemplate(
            'amfollowup_customer_wishlist_winter_modern',
            'Amasty Follow Up Email: Customer Wish List Product Added modern winter theme'
        );
        $helper->createTemplate(
            'amfollowup_customer_wishlist_autumn_modern',
            'Amasty Follow Up Email: Customer Wish List Product Added modern autumn theme'
        );
        $helper->createTemplate(
            'amfollowup_customer_wishlist_summer_modern',
            'Amasty Follow Up Email: Customer Wish List Product Added modern summer theme'
        );
        $helper->createTemplate(
            'amfollowup_customer_wishlist_spring_modern',
            'Amasty Follow Up Email: Customer Wish List Product Added modern spring theme'
        );
        $helper->createTemplate(
            'amfollowup_customer_wishlist_christmas_1_modern',
            'Amasty Follow Up Email: Customer Wish List Product Added modern Christmas theme I'
        );
        $helper->createTemplate(
            'amfollowup_customer_wishlist_christmas_2_modern',
            'Amasty Follow Up Email: Customer Wish List Product Added modern Christmas theme II'
        );

        $helper->createTemplate(
            'amfollowup_customer_wishlist_shared_winter_modern',
            'Amasty Follow Up Email: Customer Wish List Shared modern winter theme'
        );
        $helper->createTemplate(
            'amfollowup_customer_wishlist_shared_autumn_modern',
            'Amasty Follow Up Email: Customer Wish List Shared modern autumn theme'
        );
        $helper->createTemplate(
            'amfollowup_customer_wishlist_shared_summer_modern',
            'Amasty Follow Up Email: Customer Wish List Shared modern summer theme'
        );
        $helper->createTemplate(
            'amfollowup_customer_wishlist_shared_spring_modern',
            'Amasty Follow Up Email: Customer Wish List Shared modern spring theme'
        );
        $helper->createTemplate(
            'amfollowup_customer_wishlist_shared_christmas_1_modern',
            'Amasty Follow Up Email: Customer Wish List Shared modern Christmas theme I'
        );
        $helper->createTemplate(
            'amfollowup_customer_wishlist_shared_christmas_2_modern',
            'Amasty Follow Up Email: Customer Wish List Shared modern Christmas theme II'
        );

        $helper->createTemplate(
            'amfollowup_customer_wishlist_sale_winter_modern',
            'Amasty Follow Up Email: Customer Wish List on Sale modern winter theme'
        );
        $helper->createTemplate(
            'amfollowup_customer_wishlist_sale_autumn_modern',
            'Amasty Follow Up Email: Customer Wish List on Sale modern autumn theme'
        );
        $helper->createTemplate(
            'amfollowup_customer_wishlist_sale_summer_modern',
            'Amasty Follow Up Email: Customer Wish List on Sale modern summer theme'
        );
        $helper->createTemplate(
            'amfollowup_customer_wishlist_sale_spring_modern',
            'Amasty Follow Up Email: Customer Wish List on Sale modern spring theme'
        );
        $helper->createTemplate(
            'amfollowup_customer_wishlist_sale_christmas_1_modern',
            'Amasty Follow Up Email: Customer Wish List on Sale modern Christmas theme I'
        );
        $helper->createTemplate(
            'amfollowup_customer_wishlist_sale_christmas_2_modern',
            'Amasty Follow Up Email: Customer Wish List on Sale modern Christmas theme II'
        );

        $helper->createTemplate(
            'amfollowup_customer_wishlist_back_instock_winter_modern',
            'Amasty Follow Up Email: Customer Wish List Product Back In Stock modern winter theme'
        );
        $helper->createTemplate(
            'amfollowup_customer_wishlist_back_instock_autumn_modern',
            'Amasty Follow Up Email: Customer Wish List Product Back In Stock modern autumn theme'
        );
        $helper->createTemplate(
            'amfollowup_customer_wishlist_back_instock_summer_modern',
            'Amasty Follow Up Email: Customer Wish List Product Back In Stock modern summer theme'
        );
        $helper->createTemplate(
            'amfollowup_customer_wishlist_back_instock_spring_modern',
            'Amasty Follow Up Email: Customer Wish List Product Back In Stock modern spring theme'
        );
        $helper->createTemplate(
            'amfollowup_customer_wishlist_back_instock_christmas_1_modern',
            'Amasty Follow Up Email: Customer Wish List Product Back In Stock modern Christmas theme I'
        );
        $helper->createTemplate(
            'amfollowup_customer_wishlist_back_instock_christmas_2_modern',
            'Amasty Follow Up Email: Customer Wish List Product Back In Stock modern Christmas theme II'
        );

        return $this;
    }
}
