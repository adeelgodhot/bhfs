<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Model\Label\Text\Processors;

use Amasty\Label\Api\Data\LabelInterface;
use Amasty\Label\Model\Label\Text\ProcessorInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class ProductAttributesProcessor implements ProcessorInterface
{
    /**
     * @var TimezoneInterface
     */
    private $timezone;

    public function __construct(
        TimezoneInterface $timezone
    ) {
        $this->timezone = $timezone;
    }

    public function getAcceptableVariables(): array
    {
        return [
            self::ALL_VARIABLES_FLAG
        ];
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     *
     * @param string $variable
     * @param LabelInterface $label
     * @param ProductInterface $product
     * @return string
     * @throws \Exception
     */
    public function getVariableValue(string $variable, LabelInterface $label, ProductInterface $product): string
    {
        /** @var Product $product **/
        $str = 'ATTR:';
        $value = '';

        if (substr($variable, 0, strlen($str)) == $str) {
            $code  = trim(substr($variable, strlen($str)));

            $decimal = null;

            if (false !== strpos($code, ':')) {
                $temp = explode(':', $code);
                $code = $temp[0];
                $decimal = $temp[1];
            }

            $attribute = $product->getResource()->getAttribute($code);

            if ($attribute && in_array($attribute->getFrontendInput(), ['select', 'multiselect'])) {
                $value = $product->getAttributeText($code);
                if (is_array($value)) {
                    $value = implode(',', $value);
                }
            } else {
                $value = $product->getData($code);
            }

            if ($decimal !== null && false !== strpos($value, '.')) {
                $temp = explode('.', $value);
                $value = $temp[0] . '.' . substr($temp[1], 0, $decimal);
            }

            $value = (string) $value;
            if ($value && preg_match("/([0-9]{4})-([0-9]{2})-([0-9]{2})/", $value)) {
                $value = $this->timezone->formatDateTime(
                    new \DateTime($value),
                    \IntlDateFormatter::MEDIUM,
                    \IntlDateFormatter::NONE
                );
            }
        }

        return (string) $value;
    }
}
