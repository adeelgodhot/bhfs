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
use Amasty\Paction\Model\Source\Append;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Attribute\Repository as ProductAttributeRepository;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;

class Appendtext extends Command
{
    const TYPE = 'appendtext';
    const MODIFICATOR = '->';
    const FIELD = 'value';

    /**
     * @var ProductAttributeRepository
     */
    protected $productAttributeRepository;

    /**
     * @var EntityResolver
     */
    private $entityResolver;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @var ResourceConnection
     */
    private $resource;

    public function __construct(
        ResourceConnection $resource,
        ProductAttributeRepository $productAttributeRepository,
        EntityResolver $entityResolver,
        ConfigProvider $configProvider
    ) {
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        $this->productAttributeRepository = $productAttributeRepository;
        $this->entityResolver = $entityResolver;
        $this->configProvider = $configProvider;

        $this->type = self::TYPE;
        $this->info = [
            'confirm_title' => __('Append Text')->render(),
            'confirm_message' => __('Are you sure you want to append text?')->render(),
            'type' => $this->type,
            'label' => __('Append Text')->render(),
            'fieldLabel' => __('Append')->render(),
            'placeholder' => __('attribute_code->text')->render()
        ];
    }

    public function execute(array $ids, int $storeId, string $val): Phrase
    {
        $row = $this->generateAppend($val);
        $this->appendText($row, $ids, $storeId);

        return __('Total of %1 products(s) have been successfully updated.', count($ids));
    }

    protected function generateAppend(string $inputText): array
    {
        $modificatorPosition = stripos($inputText, self::MODIFICATOR);

        if ($modificatorPosition === false) {
            throw new LocalizedException(__('Field must contain "' . self::MODIFICATOR . '"'));
        }
        $value = trim(substr($inputText, 0, $modificatorPosition));
        $text = substr(
            $inputText,
            (strlen($value) + strlen(self::MODIFICATOR)),
            strlen($inputText)
        );

        return [$value, $text];
    }

    protected function appendText(array $searchReplace, array $ids, int $storeId): void
    {
        list($attributeCode, $appendText) = $searchReplace;

        try {
            $attribute = $this->productAttributeRepository->get($attributeCode);
        } catch (NoSuchEntityException $e) {
            throw new LocalizedException(__('There is no product attribute with code `%1`, ', $attributeCode));
        }

        $set = $this->addSetSql($this->connection->quote($appendText), $storeId, $attributeCode);
        $table = $this->resource->getTableName('catalog_product_entity');
        $entityIdName = $this->entityResolver->getEntityLinkField(ProductInterface::class);
        $conditions[$entityIdName . ' IN (?)'] = $ids;

        if ($attribute->getBackendType() !== AbstractAttribute::TYPE_STATIC) {
            $table = $this->resource->getTableName('catalog_product_entity_' . $attribute->getBackendType());
            $conditions['store_id = ?'] = $storeId;
            $conditions['attribute_id = ?'] = $attribute->getAttributeId();
        }
        $this->connection->update(
            $table,
            $set,
            $conditions
        );
    }

    protected function addSetSql(string $appendText, int $storeId, string $attributeCode): array
    {
        $field = $attributeCode == 'sku' ? 'sku' : self::FIELD;
        $position = $this->configProvider->getAppendTextPosition($storeId);

        if ($position == Append::POSITION_BEFORE) {
            $firstPart = $appendText;
            $secondPart = $field;
        } else {
            $firstPart = $field;
            $secondPart = $appendText;
        }

        return [$field => new \Zend_Db_Expr(sprintf(' CONCAT(%s, %s)', $firstPart, $secondPart))];
    }
}
