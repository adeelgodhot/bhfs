<?php
/**
 * Copyright © Mageside. All rights reserved.
 * See MS-LICENSE.txt for license details.
 */
namespace Mageside\Recipe\Model\ResourceModel\RecipeProduct;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            \Mageside\Recipe\Model\RecipeProduct::class,
            \Mageside\Recipe\Model\ResourceModel\RecipeProduct::class
        );
    }
}
