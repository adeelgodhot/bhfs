<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Paction
 */

declare(strict_types=1);

namespace Amasty\Paction\Model\Command;

use Amasty\Paction\Model\Command;
use Amasty\Paction\Model\ConfigProvider;
use Amasty\Paction\Model\EntityResolver;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

class Replacetext extends Command
{
    const REPLACE_MODIFICATOR = '->';
    const REPLACE_FIELD = 'value';
    const TYPE = 'replacetext';

    /**
     * @var Config
     */
    protected $eavConfig;

    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @var EntityResolver
     */
    private $entityResolver;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var ResourceConnection
     */
    private $resource;

    public function __construct(
        Config $eavConfig,
        ResourceConnection $resource,
        EntityResolver $entityResolver,
        ConfigProvider $configProvider
    ) {
        $this->eavConfig = $eavConfig;
        $this->connection = $resource->getConnection();
        $this->entityResolver = $entityResolver;
        $this->configProvider = $configProvider;
        $this->resource = $resource;

        $this->type = self::TYPE;
        $this->info = [
            'confirm_title' => __('Replace Text')->render(),
            'confirm_message' => __('Are you sure you want to replace text?')->render(),
            'type' => $this->type,
            'label' => __('Replace Text')->render(),
            'fieldLabel' => __('Replace')->render(),
            'placeholder' => __('search->replace')->render()
        ];
    }

    public function execute(array $ids, int $storeId, string $val): Phrase
    {
        $searchReplace = $this->generateReplaces($val);
        $this->searchAndReplace($searchReplace, $ids, $storeId);

        return __('Total of %1 products(s) have been successfully updated.', count($ids));
    }

    protected function generateReplaces(string $inputText): array
    {
        $modificatorPosition = stripos($inputText, self::REPLACE_MODIFICATOR);

        if ($modificatorPosition === false) {
            throw new LocalizedException(__('Replace field must contain: search->replace'));
        }
        $search = trim(
            substr($inputText, 0, $modificatorPosition)
        );
        $replace = trim(
            substr(
                $inputText,
                (strlen($search) + strlen(self::REPLACE_MODIFICATOR)),
                strlen($inputText)
            )
        );

        return [$search, $replace];
    }

    protected function searchAndReplace(array $searchReplace, array $ids, int $storeId): void
    {
        list($search, $replace) = $searchReplace;
        $attrGroups = $this->getAttrGroups();

        $table = $this->resource->getTableName('catalog_product_entity');
        $entityIdName = $this->entityResolver->getEntityLinkField(ProductInterface::class);
        $set = [];
        $conditions[$entityIdName . ' IN (?)'] = $ids;

        foreach ($attrGroups as $backendType => $attrIds) {
            if ($backendType === AbstractAttribute::TYPE_STATIC) {
                foreach ($attrIds as $attrId => $attrName) {
                    $set[$attrName] = $this->getSetSql($attrName, $search, $replace);
                }
            } else {
                $table = $this->resource->getTableName('catalog_product_entity_' . $backendType);
                $set[self::REPLACE_FIELD] = $this->getSetSql(self::REPLACE_FIELD, $search, $replace);
                $conditions['store_id = ?'] = $storeId;
                $conditions['attribute_id IN (?)'] = implode(',', array_keys($attrIds));
            }
            $this->connection->update(
                $table,
                $set,
                $conditions
            );
        }
    }

    protected function getSetSql(string $attrName, string $search, string $replace): \Zend_Db_expr
    {
        return new \Zend_Db_expr(sprintf(
            'REPLACE(`%s`, %s, %s)',
            $attrName,
            $this->connection->quote($search),
            $this->connection->quote($replace)
        ));
    }

    protected function getAttrGroups(): array
    {
        $productAttributes = $this->configProvider->getReplaceAttributes();
        $attrGroups = [];

        foreach ($productAttributes as $item) {
            $attribute = $this->eavConfig->getAttribute(Product::ENTITY, $item);
            $attrGroups[$attribute->getBackendType()][$attribute->getId()] = $attribute->getName();
        }

        return $attrGroups;
    }
}
