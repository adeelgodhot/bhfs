<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Plugin\Helper\Product;

use Amasty\RecurringPayments\Model\ConfigurableOptions;
use Amasty\RecurringPayments\Model\QuoteValidate;
use Magento\Catalog\Helper\Product\Configuration;
use Magento\Quote\Api\Data\CartItemInterface;

class ConfigurationPlugin
{
    /**
     * @var ConfigurableOptions
     */
    private $configurableOptions;

    /**
     * @var QuoteValidate
     */
    private $quoteValidate;

    public function __construct(ConfigurableOptions $configurableOptions, QuoteValidate $quoteValidate)
    {
        $this->configurableOptions = $configurableOptions;
        $this->quoteValidate = $quoteValidate;
    }

    /**
     * @param Configuration $configuration
     * @param array $result
     * @param $item
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function afterGetCustomOptions(
        Configuration $configuration,
        array $result,
        $item
    ): array {
        if ($item instanceof CartItemInterface && $this->quoteValidate->validateQuoteItem($item)) {
            $customOptions = $this->configurableOptions->getCustomOptions($item);

            if ($customOptions) {
                $result = array_merge($result, $customOptions);
            }
        }

        return $result;
    }
}
