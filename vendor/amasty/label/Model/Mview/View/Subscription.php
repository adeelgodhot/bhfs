<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Model\Mview\View;

use Magento\Framework\Mview\View\Subscription as MviewSubscription;

class Subscription extends MviewSubscription
{
    public function create(): Subscription
    {
        if ($this->isSubscriptionTableExist()) {
            parent::create();
        }

        return $this;
    }

    public function remove(): Subscription
    {
        if ($this->isSubscriptionTableExist()) {
            parent::remove();
        }

        return $this;
    }

    public function isSubscriptionTableExist(): bool
    {
        return $this->resource->getConnection()->isTableExists($this->resource->getTableName($this->tableName));
    }
}
