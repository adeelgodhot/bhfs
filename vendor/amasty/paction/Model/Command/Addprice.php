<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Paction
 */

declare(strict_types=1);

namespace Amasty\Paction\Model\Command;

use Amasty\Paction\Model\ConfigProvider;
use Amasty\Paction\Model\EntityResolver;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Config;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Store\Model\StoreManagerInterface;

class Addprice extends Modifyprice
{
    const TYPE = 'addprice';

    /**
     * @var string
     */
    protected $sourceAttributeCode = 'cost';

    /**
     * @var string
     */
    protected $attributeCodeToModify = 'price';

    public function __construct(
        Config $eavConfig,
        StoreManagerInterface $storeManager,
        ResourceConnection $resource,
        EntityResolver $entityResolver,
        ConfigProvider $configProvider
    ) {
        parent::__construct($eavConfig, $storeManager, $resource, $entityResolver, $configProvider);

        $this->type = self::TYPE;
        $this->info = array_merge($this->info, [
            'confirm_title' => __('Modify Price using Cost')->render(),
            'confirm_message' => __('Are you sure you want to modify price using cost?')->render(),
            'type' => $this->type,
            'label' => __('Modify Price using Cost')->render()
        ]);
    }

    protected function prepareQuery(string $table, string $value, array $where): string
    {
        $attributeId = $this->eavConfig
            ->getAttribute(Product::ENTITY, $this->attributeCodeToModify)
            ->getAttributeId();
        $entityIdName = $this->entityResolver->getEntityLinkField(ProductInterface::class);
        $value = str_replace('`value`', 't.`value`', $value);
        $fields = ['attribute_id', 'store_id', $entityIdName, 'value'];
        $select = $this->connection->select()
            ->from(['t' => $table])
            ->reset('columns')
            ->columns([new \Zend_Db_expr((int)$attributeId), 'store_id', $entityIdName, new \Zend_Db_expr($value)])
            ->where('t.value > 0 ');

        foreach ($where as $part) {
            $select->where($part);
        }

        return $this->connection->insertFromSelect(
            $select,
            $table,
            $fields,
            AdapterInterface::INSERT_ON_DUPLICATE
        );
    }
}
