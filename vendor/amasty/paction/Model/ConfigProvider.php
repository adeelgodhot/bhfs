<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Paction
 */

declare(strict_types=1);

namespace Amasty\Paction\Model;

use Amasty\Base\Model\ConfigProviderAbstract;

class ConfigProvider extends ConfigProviderAbstract
{
    protected $pathPrefix = 'amasty_paction/';

    const COMMANDS = 'general/commands';
    const PRICE_ROUNDING_TYPE = 'general/round';
    const ROUNDING_VALUE = 'general/fixed';
    const COPY_ATTRIBUTES = 'general/attr';
    const REPLACE_IN_ATTRIBUTES = 'general/replace_in_attr';
    const APPEND_TEXT_POSITION = 'general/append_text_position';

    const RELATE_TYPE = 'links/related';
    const RELATE_DIRECTION = 'links/related_reverse';
    const UPSELL_TYPE = 'links/upsell';
    const UPSELL_DIRECTION = 'links/upsell_reverse';
    const CROSSELL_TYPE = 'links/crosssell';
    const CROSSELL_DIRECTION = 'links/crosssell_reverse';

    public function getCommands($storeId = null): array
    {
        $commands = [];

        if ($value = $this->getValue(self::COMMANDS, $storeId)) {
            $commands = explode(',', $value);
        }

        return $commands;
    }

    public function getPriceRoundingType($storeId = null): string
    {
        return $this->getValue(self::PRICE_ROUNDING_TYPE, $storeId);
    }

    public function getRoundingValue($storeId = null): float
    {
        return (float)$this->getValue(self::ROUNDING_VALUE, $storeId);
    }

    public function getCopyAttributes($storeId = null): array
    {
        $attributes = [];

        if ($value = $this->getValue(self::COPY_ATTRIBUTES, $storeId)) {
            $attributes = explode(',', $value);
        }

        return $attributes;
    }

    public function getReplaceAttributes($storeId = null): array
    {
        $attributes = [];

        if ($value = $this->getValue(self::REPLACE_IN_ATTRIBUTES, $storeId)) {
            $attributes = explode(',', $value);
        }

        return $attributes;
    }

    public function getAppendTextPosition($storeId = null): string
    {
        return $this->getValue(self::APPEND_TEXT_POSITION, $storeId);
    }

    public function getLinkType(string $link, $storeId = null): ?int
    {
        switch ($link) {
            case 'related':
                $type = (int)$this->getValue(self::RELATE_TYPE, $storeId);
                break;
            case 'upsell':
                $type = (int)$this->getValue(self::UPSELL_TYPE, $storeId);
                break;
            case 'crosssell':
                $type = (int)$this->getValue(self::CROSSELL_TYPE, $storeId);
                break;
            default:
                $type = null;
        }

        return $type;
    }

    public function getLinkDirection(string $link, $storeId = null): ?int
    {
        switch ($link) {
            case 'related':
                $direction = (int)$this->getValue(self::RELATE_DIRECTION, $storeId);
                break;
            case 'upsell':
                $direction = (int)$this->getValue(self::UPSELL_DIRECTION, $storeId);
                break;
            case 'crosssell':
                $direction = (int)$this->getValue(self::CROSSELL_DIRECTION, $storeId);
                break;
            default:
                $direction = null;
        }

        return $direction;
    }
}
