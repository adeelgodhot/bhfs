<?php
/**
 * Copyright © Mageside. All rights reserved.
 * See MS-LICENSE.txt for license details.
 */
namespace Mageside\Recipe\Model\ResourceModel\Review;

class Collection extends \Magento\Review\Model\ResourceModel\Review\Collection
{
    protected $_idFieldName = 'main_table.review_id';
}
