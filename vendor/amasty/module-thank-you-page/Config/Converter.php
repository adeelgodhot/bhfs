<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ThankYouPage
 */

namespace Amasty\ThankYouPage\Config;

/**
 * Converter of block_types.xml content into array format
 */
class Converter implements \Magento\Framework\Config\ConverterInterface
{

    /**
     * @var array
     */
    private static $convertFields = ['title', 'class_name', 'template'];

    /**
     * @param \DOMDocument $source
     *
     * @return array
     */
    public function convert($source)
    {
        $types = [];
        /** @var \DOMNodeList $config */
        $config = $source->getElementsByTagName('block_types');

        /** @var \DOMElement $configItem */
        foreach ($config as $configItem) {
            foreach ($configItem->childNodes as $configType) {
                if ($configType->nodeType != XML_ELEMENT_NODE) {
                    continue;
                }

                $item = [];
                foreach (self::$convertFields as $field) {
                    if ($node = $configType->getElementsByTagName($field)->item(0)) {
                        $item[$field] = $node->nodeValue;
                    }
                }

                $types[$configType->attributes->getNamedItem('id')->nodeValue] = $item;
            }
        }

        return ['types' => $types];
    }
}
