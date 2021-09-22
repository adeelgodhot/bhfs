<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingCalculator
 */


namespace Amasty\ShippingCalculator\Model;

use Amasty\Base\Model\ConfigProviderAbstract;

class ConfigProvider extends ConfigProviderAbstract
{
    const ENABLED_XPATH = 'general/enabled';
    const DISPLAY_OPTIONS_PLACES_XPATH = 'display_options/places';
    const DISPLAY_OPTIONS_TAB_NAME_XPATH = 'display_options/tab_name';
    const DISPLAY_OPTIONS_DESCRIPTIONS_XPATH = 'display_options/description';
    const DISPLAY_OPTIONS_SHOW_COUNTRY_FIELD_XPATH = 'display_options/show_country_field';
    const DISPLAY_OPTIONS_SHOW_REGION_FIELD_XPATH = 'display_options/show_region_field';
    const DISPLAY_OPTIONS_SHOW_POSTCODE_FIELD_XPATH = 'display_options/show_postcode_field';
    const DISPLAY_OPTIONS_NOT_FOUND_MESSAGE_XPATH = 'display_options/not_found_message';
    const DEFAULT_VALUES_COUNTRY_XPATH = 'default_values/country_id';
    const DEFAULT_VALUES_REGION_XPATH = 'default_values/region_id';
    const DEFAULT_VALUES_POSTCODE_XPATH = 'default_values/postcode';
    const DISPLAY_RESTRICTIONS_PRODUCTS_XPATH = 'display_restrictions/restricted_products_ids';
    const DISPLAY_RESTRICTIONS_CATEGORIES_XPATH = 'display_restrictions/restricted_categories_ids';

    /**
     * xpath prefix of module (section)
     * @var string '{section}/'
     */
    protected $pathPrefix = 'amasty_shipping_calculator/';

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->isSetFlag(static::ENABLED_XPATH);
    }

    /**
     * @return array
     */
    public function getPlacesForDisplay()
    {
        return $this->getArrayFromCommaSeparatedConfigValue(static::DISPLAY_OPTIONS_PLACES_XPATH);
    }

    /**
     * @return string
     */
    public function getTabName()
    {
        return $this->getValue(static::DISPLAY_OPTIONS_TAB_NAME_XPATH);
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->getValue(static::DISPLAY_OPTIONS_DESCRIPTIONS_XPATH);
    }

    /**
     * @return bool
     */
    public function isShowCountyField()
    {
        return $this->isSetFlag(static::DISPLAY_OPTIONS_SHOW_COUNTRY_FIELD_XPATH);
    }

    /**
     * @return bool
     */
    public function isShowRegionField()
    {
        return $this->isSetFlag(static::DISPLAY_OPTIONS_SHOW_REGION_FIELD_XPATH);
    }

    /**
     * @return bool
     */
    public function isShowPostcodeField()
    {
        return $this->isSetFlag(static::DISPLAY_OPTIONS_SHOW_POSTCODE_FIELD_XPATH);
    }

    /**
     * @return string
     */
    public function getNotFoundMessage()
    {
        return $this->getValue(static::DISPLAY_OPTIONS_NOT_FOUND_MESSAGE_XPATH);
    }

    /**
     * @return string
     */
    public function getDefaultCountry()
    {
        return $this->getValue(static::DEFAULT_VALUES_COUNTRY_XPATH);
    }

    /**
     * @return string
     */
    public function getDefaultRegion()
    {
        return $this->getValue(static::DEFAULT_VALUES_REGION_XPATH);
    }

    /**
     * @return string
     */
    public function getDefaultPostcode()
    {
        return $this->getValue(static::DEFAULT_VALUES_POSTCODE_XPATH);
    }

    /**
     * @return array
     */
    public function getRestrictedProductsIds()
    {
        return $this->getArrayFromCommaSeparatedConfigValue(static::DISPLAY_RESTRICTIONS_PRODUCTS_XPATH);
    }

    /**
     * @return array
     */
    public function getRestrictedCategoriesIds()
    {
        return $this->getArrayFromCommaSeparatedConfigValue(static::DISPLAY_RESTRICTIONS_CATEGORIES_XPATH);
    }

    /**
     * @param string $xpath
     * @param string $scopeType
     * @return array
     */
    private function getArrayFromCommaSeparatedConfigValue($xpath)
    {
        $values = $this->getValue($xpath);
        $values = explode(',', $values);
        $values = array_map('trim', $values);
        $values = array_filter($values);

        return $values;
    }

}
