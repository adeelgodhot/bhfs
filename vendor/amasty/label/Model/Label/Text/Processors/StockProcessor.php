<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Model\Label\Text\Processors;

use Amasty\Label\Api\Data\LabelInterface;
use Amasty\Label\Exceptions\TextRenderException;
use Amasty\Label\Model\Label\Text\ProcessorInterface;
use Amasty\Label\Model\Product\GetQty;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;

class StockProcessor implements ProcessorInterface
{
    const STOCK = 'STOCK';

    /**
     * @var GetQty
     */
    private $getQty;

    public function __construct(
        GetQty $getQty
    ) {
        $this->getQty = $getQty;
    }

    public function getAcceptableVariables(): array
    {
        return [
            self::STOCK
        ];
    }

    public function getVariableValue(string $variable, LabelInterface $label, ProductInterface $product): string
    {
        switch ($variable) {
            case self::STOCK:
                return (string) $this->getProductQty($product);
            default:
                throw new TextRenderException(__('The passed variable could not be processed'));
        }
    }

    private function getProductQty(Product $product): ?float
    {
        $qty = null;

        if ($product->getTypeId() === Type::TYPE_SIMPLE) {
            if ($product->hasData('quantity_and_stock_status')) {
                $quantityAndStockstatus = $product->getData('quantity_and_stock_status');
                $qty = empty($quantityAndStockstatus['qty']) ? null : (float) $quantityAndStockstatus['qty'];
            }

            if ($qty === null && $product->hasData('qty')) {
                $qty = (float) $product->getData('qty');
            }

            if ($qty === null && $product->hasData('quantity')) {
                $qty = (float) $product->getData('quantity');
            }
        }

        if ($qty === null) {
            $qty = $this->getQty->execute($product);
        }

        return $qty;
    }
}
