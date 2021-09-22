<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPaypal
 */


declare(strict_types=1);

namespace Amasty\RecurringPaypal\Api\Data;

interface ProductInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const ID = 'id';
    const PRODUCT_ID = 'product_id';
    const PAYPAL_PRODUCT_ID = 'paypal_product_id';
    /**#@-*/

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     *
     * @return ProductInterface
     */
    public function setId($id);

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
    public function getPaypalProductId();

    /**
     * @param string|null $paypalProdId
     *
     * @return ProductInterface
     */
    public function setPaypalProductId($paypalProdId);
}
