<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */


namespace Amasty\Followup\Api;

interface RuleInterface
{
    /**@#+
     * Coupon code attributes
     */
    const COUPON_CODE_NONE = '';
    const COUPON_CODE_BY_PERCENT = 'by_percent';
    const COUPON_CODE_BY_FIXED = 'by_fixed';
    const COUPON_CODE_CART_FIXED = 'cart_fixed';
    const COUPON_CODE_USE_RULE = 'use_rule';
    /**#@-*/

    /**@#+
     * Rule statuses
     */
    const RULE_ACTIVE = '1';
    const RULE_INACTIVE = '0';
    /**#@-*/

    /**@#+
     * Lists of order events
     */
    const TYPE_ORDER_NEW = 'order_new';
    const TYPE_ORDER_SHIP = 'order_ship';
    const TYPE_ORDER_INVOICE = 'order_invoice';
    const TYPE_ORDER_COMPLETE = 'order_complete';
    const TYPE_ORDER_CANCEL = 'order_cancel';
    /**#@-*/

    /**@#+
     * Lists of customer events
     */
    const TYPE_CUSTOMER_GROUP = 'customer_group';
    const TYPE_CUSTOMER_BIRTHDAY = 'customer_birthday';
    const TYPE_CUSTOMER_NEW = 'customer_new';
    const TYPE_CUSTOMER_SUBSCRIPTION = 'customer_subscription';
    const TYPE_CUSTOMER_ACTIVITY = 'customer_activity';
    const TYPE_CUSTOMER_WISHLIST = 'customer_wishlist';
    const TYPE_CUSTOMER_WISHLIST_SHARED = 'customer_wishlist_shared';
    const TYPE_CUSTOMER_WISHLIST_SALE = 'customer_wishlist_sale';
    const TYPE_CUSTOMER_WISHLIST_BACK_INSTOCK = 'customer_wishlist_back_instock';
    const TYPE_CUSTOMER_DATE = 'customer_date';
    /**#@-*/

    /**@#+
     * Lists of cancel events
     */
    const TYPE_CANCEL_ORDER_COMPLETE = 'cancel_order_complete';
    const TYPE_CANCEL_ORDER_STATUS = 'cancel_order_status';
    const TYPE_CANCEL_CUSTOMER_LOGGEDIN = 'cancel_customer_loggedin';
    const TYPE_CANCEL_CUSTOMER_CLICKLINK = 'cancel_customer_clicklink';
    const TYPE_CANCEL_CUSTOMER_WISHLIST_SHARED = 'cancel_customer_wishlist_shared';
    /**#@-*/

    /**
     * Default event
     */
    const TYPE_BASIC = 'basic';
}
