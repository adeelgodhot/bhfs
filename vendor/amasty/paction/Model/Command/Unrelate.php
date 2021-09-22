<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Paction
 */

declare(strict_types=1);

namespace Amasty\Paction\Model\Command;

use Amasty\Paction\Model\Command;
use Amasty\Paction\Model\LinkActionsManagement;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

class Unrelate extends Command
{
    const TYPE = 'unrelated';

    /**
     * @var AdapterInterface
     */
    protected $connection;

    /**
     * @var ResourceConnection
     */
    private $resource;
    /**
     * @var LinkActionsManagement
     */
    private $linkActionsManagement;

    public function __construct(
        ResourceConnection $resource,
        LinkActionsManagement $linkActionsManagement
    ) {
        $this->connection = $resource->getConnection();

        $this->type = self::TYPE;
        $this->info = [
            'confirm_title' => __('Remove Relations')->render(),
            'confirm_message' => __('Are you sure you want to remove relations?')->render(),
            'type' => $this->type,
            'label' => __('Remove Relations')->render(),
            'fieldLabel' => __('Select Algorithm')->render()
        ];
        $this->resource = $resource;
        $this->linkActionsManagement = $linkActionsManagement;
    }

    public function execute(array $ids, int $storeId, string $val): Phrase
    {
        if (!$ids) {
            throw new LocalizedException(__('Please select product(s)'));
        }
        $table = $this->resource->getTableName('catalog_product_link');

        switch ($val) {
            case 1: // between selected
                $where = [
                    'product_id IN(?)' => $ids,
                    'linked_product_id IN(?)' => $ids,
                ];
                break;
            case 2: // selected products from all
                $where = [
                    'linked_product_id IN(?)' => $ids,
                ];
                break;
            default: // Remove all relations from selected products
                $where = [
                    'product_id IN(?)' => $ids,
                ];
        }
        $this->connection->delete(
            $table,
            array_merge($where, ['link_type_id = ?' => $this->linkActionsManagement->getLinkTypeId($this->type)])
        );

        return __('Product associations have been successfully deleted.');
    }
}
