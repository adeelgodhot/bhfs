<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringStripe
 */


declare(strict_types=1);

namespace Amasty\RecurringStripe\Api\Data;

interface ProductInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const ENTITY_ID = 'entity_id';
    const PRODUCT_ID = 'product_id';
    const STRIPE_PRODUCT_ID = 'stripe_product_id';
    const STRIPE_ACCOUNT_ID = 'stripe_account_id';
    /**#@-*/

    /**
     * @return int
     */
    public function getEntityId();

    /**
     * @param int $entityId
     *
     * @return ProductInterface
     */
    public function setEntityId($entityId);

    /**
     * @return int|null
     */
    public function getProductId();

    /**
     * @param int|null $productId
     *
     * @return ProductInterface
     */
    public function setProductId($productId);

    /**
     * @return string|null
     */
    public function getStripeProductId();

    /**
     * @param string|null $stripeProductId
     *
     * @return ProductInterface
     */
    public function setStripeProductId($stripeProductId);

    /**
     * @return string|null
     */
    public function getStripeAccountId();

    /**
     * @param string|null $stripeAccountId
     *
     * @return ProductInterface
     */
    public function setStripeAccountId($stripeAccountId);
}
