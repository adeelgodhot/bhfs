<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


declare(strict_types=1);

namespace Amasty\Reports\Model\ResourceModel\Customers\Returning\Collection;

use Magento\Framework\App\ResourceConnection;

class SelectProvider
{
    /**
     * @var ResourceConnection
     */
    private $connection;

    public function __construct(
        ResourceConnection $connection
    ) {
        $this->connection = $connection;
    }

    public function getNewCustomersQuery(): \Zend_Db_Expr
    {
        return new \Zend_Db_Expr(
            "COUNT(distinct customer_email) -
            (SELECT COUNT(distinct customer_email) FROM " . $this->connection->getTableName('sales_order') . "
            WHERE FIND_IN_SET(customer_email, GROUP_CONCAT(customerEmail))
            AND " . $this->connection->getTableName('sales_order') . ".created_at < created_date)"
        );
    }

    public function getReturningCustomersSelect(): string
    {
        return '(COUNT(entity_id) - (' . $this->getNewCustomersQuery() . '))';
    }

    public function getPercentSelect(): string
    {
        return '(ROUND((COUNT(entity_id) - (' . $this->getNewCustomersQuery() . ')) / COUNT(entity_id) * 100))';
    }
}
