<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Paction
 */

declare(strict_types=1);

namespace Amasty\Paction\Model\Command;

use Amasty\Paction\Model\Command;
use Amasty\Paction\Model\EntityResolver;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

class Amdelete extends Command
{
    const TYPE = 'amdelete';

    /**
     * @var AdapterInterface
     */
    protected $connection;

    /**
     * @var EntityResolver
     */
    private $entityResolver;

    /**
     * @var ResourceConnection
     */
    private $resource;

    public function __construct(
        ResourceConnection $resource,
        EntityResolver $entityResolver
    ) {
        $this->connection = $resource->getConnection();
        $this->resource = $resource;
        $this->entityResolver = $entityResolver;

        $this->type = self::TYPE;
        $this->info = [
            'confirm_title' => __('Fast Delete')->render(),
            'confirm_message' => __('Are you sure you want to apply Fast Delete?')->render(),
            'type' => $this->type,
            'label' => __('Fast Delete')->render(),
            'fieldLabel' => ''
        ];
    }

    public function execute(array $ids, int $storeId, string $val): Phrase
    {
        if (!$ids) {
            throw new LocalizedException(__('Please select product(s)'));
        }

        // do the bulk delete skiping all _before/_after delete observers
        // and indexing, as it cause thousands of queries in the
        // getProductParentsByChild function
        $table = $this->resource->getTableName('catalog_product_entity');
        $entityIdName = $this->entityResolver->getEntityLinkField(ProductInterface::class);

        // foreign keys delete the rest
        $this->connection->delete($table, $this->connection->quoteInto($entityIdName . ' IN(?)', $ids));

        return __(
            'Products have been successfully deleted. '
            . 'We recommend to refresh indexes at the System > Index Management page.'
        );
    }
}
