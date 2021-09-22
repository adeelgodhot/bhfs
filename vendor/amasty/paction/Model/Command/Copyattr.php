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
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;

class Copyattr extends Command
{
    const TYPE = 'copyattr';

    /**
     * @var AdapterInterface
     */
    protected $connection;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

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
        ResourceConnection $resource,
        ProductRepositoryInterface $productRepository,
        EntityResolver $entityResolver,
        ConfigProvider $configProvider
    ) {
        $this->connection = $resource->getConnection();
        $this->resource = $resource;
        $this->productRepository = $productRepository;
        $this->entityResolver = $entityResolver;
        $this->configProvider = $configProvider;

        $this->type = self::TYPE;
        $this->info = [
            'confirm_title' => __('Copy Attributes')->render(),
            'confirm_message' => __('Are you sure you want to copy attributes?')->render(),
            'type' => $this->type,
            'label' => __('Copy Attributes')->render(),
            'fieldLabel' => __('From')->render(),
            'placeholder' => __('ID of product')->render()
        ];
    }

    public function execute(array $ids, int $storeId, string $val): Phrase
    {
        $fromId = (int)trim($val);

        try {
            $fromProduct = $this->productRepository->getById($fromId, false, $storeId);
        } catch (NoSuchEntityException $e) {
            throw new LocalizedException(__('Please provide a valid product ID'));
        }

        if (!$fromProduct->getId()) {
            throw new LocalizedException(__('Please provide a valid product ID'));
        }
        $fromId = $this->entityResolver->getEntityLinkIds(ProductInterface::class, [$fromId]);

        if (isset($fromId[0])) {
            $fromId = (int)$fromId[0];
        }

        if (!$fromId) {
            throw new LocalizedException(__('Please provide a valid product ID'));
        }

        if (in_array($fromId, $ids)) {
            throw new LocalizedException(__('Please remove source product from the selected products'));
        }
        $codes = $this->configProvider->getCopyAttributes($storeId);

        if (!$codes) {
            throw new LocalizedException(__('Please set attribute codes in the module configuration'));
        }
        $config = [];

        foreach ($codes as $code) {
            $code = trim($code);
            /** @var \Magento\Eav\Model\Entity\Attribute $attribute */
            $attribute = $fromProduct->getResource()->getAttribute($code);

            if (!$attribute || !$attribute->getId()) {
                $message = __(
                    'There is no product attribute with code `%1`, '
                    . 'please compare values in the module configuration with stores > attributes > product.',
                    $code
                );
                throw new LocalizedException($message);
            }

            if ($attribute->getIsUnique()) {
                $message = __(
                    'Attribute `%1` is unique and can not be copied. '
                    . 'Please remove the code in the module configuration.',
                    $code
                );
                throw new LocalizedException($message);
            }
            $type = $attribute->getBackendType();

            if ($type === AbstractAttribute::TYPE_STATIC) {
                $message = __(
                    'Attribute `%1` is static and can not be copied. '
                    . 'Please remove the code in the module configuration.',
                    $code
                );
                throw new LocalizedException($message);
            }

            if (!isset($config[$type])) {
                $config[$type] = [];
            }
            $config[$type][] = $attribute->getId();
        }
        // we do not use store id as it is global action
        $this->copyData($fromId, $ids, $config);

        return __('Attributes have been successfully copied.');
    }

    protected function copyData(int $fromId, array $ids, array $config): void
    {
        $entityIdName = $this->entityResolver->getEntityLinkField(ProductInterface::class);

        foreach ($config as $type => $attributes) {
            if (!$attributes) {
                continue;
            }

            $table = $this->resource->getTableName('catalog_product_entity_' . $type);
            $fields = ['attribute_id', 'store_id', $entityIdName, 'value'];

            foreach ($ids as $id) {
                $data = $this->connection->select()
                    ->from(['t' => $table])
                    ->reset('columns')
                    ->columns(['attribute_id', 'store_id', new \Zend_Db_Expr((int)$id), 'value'])
                    ->where("t.$entityIdName = ?", $fromId)
                    ->where('t.attribute_id IN(?)', $attributes);
                $this->connection->query(
                    $this->connection->insertFromSelect(
                        $data,
                        $table,
                        $fields,
                        AdapterInterface::INSERT_ON_DUPLICATE
                    )
                );
            }
        }
    }
}
