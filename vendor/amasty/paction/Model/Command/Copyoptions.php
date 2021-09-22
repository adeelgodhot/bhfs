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
use Amasty\Paction\Model\GetProductCollectionByIds;
use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory;
use Magento\Catalog\Api\Data\ProductCustomOptionValuesInterfaceFactory;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Config\Source\ProductPriceOptionsInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Model\Product\Option\Value;
use Magento\Catalog\Model\Product\OptionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

class Copyoptions extends Command
{
    const TYPE = 'copyoptions';

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var OptionFactory
     */
    private $optionFactory;

    /**
     * @var ProductCustomOptionInterfaceFactory
     */
    private $productCustomOptionInterfaceFactory;

    /**
     * @var ProductCustomOptionValuesInterfaceFactory
     */
    private $productCustomOptionValuesInterfaceFactory;

    /**
     * @var EntityResolver
     */
    private $entityResolver;

    /**
     * @var GetProductCollectionByIds
     */
    private $getProductCollectionByIds;

    /**
     * @var array
     */
    private $parentOptions = [];

    /**
     * @var array
     */
    private $customOptionTypes = [
        ProductCustomOptionInterface::OPTION_TYPE_FIELD,
        ProductCustomOptionInterface::OPTION_TYPE_AREA,
        ProductCustomOptionInterface::OPTION_TYPE_DROP_DOWN,
        ProductCustomOptionInterface::OPTION_TYPE_CHECKBOX,
        ProductCustomOptionInterface::OPTION_TYPE_MULTIPLE,
        ProductCustomOptionInterface::OPTION_TYPE_RADIO,
        ProductCustomOptionInterface::OPTION_TYPE_FILE,
        ProductCustomOptionInterface::OPTION_TYPE_DATE,
        ProductCustomOptionInterface::OPTION_TYPE_DATE_TIME,
        ProductCustomOptionInterface::OPTION_TYPE_TIME
    ];

    /**
     * @var array
     */
    private $typesWithOptions = [
        ProductCustomOptionInterface::OPTION_TYPE_DROP_DOWN,
        ProductCustomOptionInterface::OPTION_TYPE_CHECKBOX,
        ProductCustomOptionInterface::OPTION_TYPE_MULTIPLE,
        ProductCustomOptionInterface::OPTION_TYPE_RADIO
    ];

    /**
     * @var array
     */
    private $commonKeys = [
        Option::KEY_TYPE,
        Option::KEY_TITLE,
        Option::KEY_IS_REQUIRE,
        Option::KEY_SORT_ORDER,
        'is_delete',
        'previous_type',
        'previous_group',
        'values'
    ];

    /**
     * @var array
     */
    private $priceKeys = [
        Option::KEY_PRICE_TYPE,
        Option::KEY_PRICE,
        Option::KEY_SKU
    ];

    /**
     * @var array
     */
    private $fileKeys = [
        Option::KEY_FILE_EXTENSION,
        Option::KEY_IMAGE_SIZE_X,
        Option::KEY_IMAGE_SIZE_Y
    ];

    /**
     * @var array
     */
    private $txtKeys = [Option::KEY_MAX_CHARACTERS];

    /**
     * @var array
     */
    private $optionValueKeys = [
        Value::KEY_TITLE,
        Value::KEY_SORT_ORDER,
        Value::KEY_PRICE_TYPE,
        Value::KEY_PRICE,
        Value::KEY_SKU,
        'is_delete'
    ];

    public function __construct(
        ProductRepositoryInterface $productRepository,
        OptionFactory $optionFactory,
        ProductCustomOptionInterfaceFactory $productCustomOptionInterfaceFactory,
        ProductCustomOptionValuesInterfaceFactory $productCustomOptionValuesInterfaceFactory,
        EntityResolver $entityResolver,
        GetProductCollectionByIds $getProductCollectionByIds
    ) {
        $this->productRepository = $productRepository;
        $this->optionFactory = $optionFactory;
        $this->productCustomOptionInterfaceFactory = $productCustomOptionInterfaceFactory;
        $this->productCustomOptionValuesInterfaceFactory = $productCustomOptionValuesInterfaceFactory;
        $this->entityResolver = $entityResolver;

        $this->type = self::TYPE;
        $this->info = [
            'confirm_title' => __('Copy Custom Options')->render(),
            'confirm_message' => __('Are you sure you want to copy custom options?')->render(),
            'type' => $this->type,
            'label' => __('Copy Custom Options')->render(),
            'fieldLabel' => __('From')->render(),
            'placeholder' => __('Product ID')->render()
        ];
        $this->getProductCollectionByIds = $getProductCollectionByIds;
    }

    public function execute(array $ids, int $storeId, string $val): Phrase
    {
        if (empty($ids)) {
            throw new LocalizedException(__('Please select product(s)'));
        }
        $parentProductId = (int)trim($val);

        if (!$parentProductId) {
            throw new LocalizedException(__('Please provide a valid product ID'));
        }
        $parentProduct = $this->productRepository->getById($parentProductId);
        $this->parentOptions = $this->getOptionsAsArray($parentProduct);

        if (empty($this->parentOptions)) {
            throw new LocalizedException(__('Please provide a product with custom options'));
        }
        $entityIdName = $this->entityResolver->getEntityLinkField(ProductInterface::class);
        $num = 0;

        /** @var Product $product */
        foreach ($this->getProductCollectionByIds->get($ids, $entityIdName) as $product) {
            if ($parentProductId == $product->getId()) {
                continue;
            }
            try {
                foreach ($this->parentOptions as $option) {
                    /** @var ProductCustomOptionInterface $customOption */
                    $customOption = $this->productCustomOptionInterfaceFactory->create(['data' => $option]);
                    $customOption->setProductSku($product->getSku());

                    if (isset($option['values'])) {
                        $customOption->setValues($this->getOptionValues($option['values']));
                    }
                    $product->addOption($customOption);
                }
                $product->setIsMassupdate(true);
                $product->setExcludeUrlRewrite(true);
                $product->setCanSaveCustomOptions(true);
                $product->setHasOptions(true);

                $this->productRepository->save($product);
                ++$num;
            } catch (\Exception $error) {
                $this->errors[] = __(
                    'Can not copy the options to the product ID=%1, the error is: %2',
                    $product->getId(),
                    $error->getMessage()
                );
            }
        }

        return __('Total of %1 products(s) have been successfully updated.', $num);
    }

    private function getOptionValues(array $optionValues): array
    {
        $values = [];

        foreach ($optionValues as $value) {
            if (!$value['price_type']) {
                $value['price_type'] = ProductPriceOptionsInterface::VALUE_FIXED;
            }

            $value = $this->productCustomOptionValuesInterfaceFactory->create(['data' => $value]);
            $values[] = $value;
        }

        return $values;
    }

    private function getOptionsAsArray(Product $product): array
    {
        $collection = $this->optionFactory->create()->getProductOptionCollection($product);
        $options = [];

        foreach ($collection as $option) {
            if (in_array($option->getType(), $this->customOptionTypes)) {
                $options[] = $this->convertToArray($option);
            }
        }

        return $options;
    }

    private function convertToArray(Option $option): array
    {
        $type = $option->getType();

        switch ($type) {
            case ProductCustomOptionInterface::OPTION_TYPE_FILE:
                $optionKeys = array_merge($this->commonKeys, $this->priceKeys, $this->fileKeys);
                break;
            case ProductCustomOptionInterface::OPTION_TYPE_FIELD:
            case ProductCustomOptionInterface::OPTION_TYPE_AREA:
                $optionKeys = array_merge($this->commonKeys, $this->priceKeys, $this->txtKeys);
                break;
            case ProductCustomOptionInterface::OPTION_TYPE_DATE:
            case ProductCustomOptionInterface::OPTION_TYPE_DATE_TIME:
            case ProductCustomOptionInterface::OPTION_TYPE_TIME:
                $optionKeys = array_merge($this->commonKeys, $this->priceKeys);
                break;
            default:
                $optionKeys = $this->commonKeys;
        }
        $optionAsArray = $option->toArray($optionKeys);

        if (in_array($type, $this->typesWithOptions)) {
            $optionAsArray['values'] = [];

            foreach ($option->getValues() as $value) {
                $optionAsArray['values'][] = $value->toArray($this->optionValueKeys);
            }
        }

        return $optionAsArray;
    }
}
