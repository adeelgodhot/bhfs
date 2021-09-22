<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model;

use Amasty\RecurringPayments\Api\Subscription\AddressInterface;
use Amasty\RecurringPayments\Model\ResourceModel\Address as AddressResource;
use Magento\Framework\Model\AbstractModel;

class Address extends AbstractModel implements AddressInterface
{
    public function _construct()
    {
        $this->_init(AddressResource::class);
    }

    /**
     * @inheritDoc
     */
    public function setSubscriptionId(string $subscriptionId): AddressInterface
    {
        $this->setData(AddressInterface::SUBSCRIPTION_ID, $subscriptionId);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getSubscriptionId()
    {
        return $this->_getData(AddressInterface::SUBSCRIPTION_ID);
    }

    /**
     * @inheritDoc
     */
    public function getRegion()
    {
        return $this->_getData(AddressInterface::KEY_REGION);
    }

    /**
     * @inheritDoc
     */
    public function setRegion($region)
    {
        $this->setData(AddressInterface::KEY_REGION, $region);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getRegionId()
    {
        return $this->_getData(AddressInterface::KEY_REGION_ID);
    }

    /**
     * @inheritDoc
     */
    public function setRegionId($regionId)
    {
        $this->setData(AddressInterface::KEY_REGION_ID, $regionId);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getRegionCode()
    {
        return $this->_getData(AddressInterface::KEY_REGION_CODE);
    }

    /**
     * @inheritDoc
     */
    public function setRegionCode($regionCode)
    {
        $this->setData(AddressInterface::KEY_REGION_CODE, $regionCode);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCountryId()
    {
        return $this->_getData(AddressInterface::KEY_COUNTRY_ID);
    }

    /**
     * @inheritDoc
     */
    public function setCountryId($countryId)
    {
        $this->setData(AddressInterface::KEY_COUNTRY_ID, $countryId);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setCountry($country)
    {
        $this->setData(AddressInterface::KEY_COUNTRY, $country);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getStreet()
    {
        return $this->_getData(AddressInterface::KEY_STREET);
    }

    /**
     * @inheritDoc
     */
    public function setStreet($street)
    {
        $this->setData(AddressInterface::KEY_STREET, $street);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getTelephone()
    {
        return $this->_getData(AddressInterface::KEY_TELEPHONE);
    }

    /**
     * @inheritDoc
     */
    public function setTelephone($telephone)
    {
        $this->setData(AddressInterface::KEY_TELEPHONE, $telephone);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getPostcode()
    {
        return $this->_getData(AddressInterface::KEY_POSTCODE);
    }

    /**
     * @inheritDoc
     */
    public function setPostcode($postcode)
    {
        $this->setData(AddressInterface::KEY_POSTCODE, $postcode);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCity()
    {
        return $this->_getData(AddressInterface::KEY_CITY);
    }

    /**
     * @inheritDoc
     */
    public function setCity($city)
    {
        $this->setData(AddressInterface::KEY_CITY, $city);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getFirstname()
    {
        return $this->_getData(AddressInterface::KEY_FIRSTNAME);
    }

    /**
     * @inheritDoc
     */
    public function setFirstname($firstname)
    {
        $this->setData(AddressInterface::KEY_FIRSTNAME, $firstname);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getLastname()
    {
        return $this->_getData(AddressInterface::KEY_LASTNAME);
    }

    /**
     * @inheritDoc
     */
    public function setLastname($lastname)
    {
        $this->setData(AddressInterface::KEY_LASTNAME, $lastname);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getMiddlename()
    {
        return $this->_getData(AddressInterface::KEY_MIDDLENAME);
    }

    /**
     * @inheritDoc
     */
    public function setMiddlename($middlename)
    {
        $this->setData(AddressInterface::KEY_MIDDLENAME, $middlename);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getPrefix()
    {
        return $this->_getData(AddressInterface::KEY_PREFIX);
    }

    /**
     * @inheritDoc
     */
    public function setPrefix($prefix)
    {
        $this->setData(AddressInterface::KEY_PREFIX, $prefix);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getSuffix()
    {
        return $this->_getData(AddressInterface::KEY_SUFFIX);
    }

    /**
     * @inheritDoc
     */
    public function setSuffix($suffix)
    {
        $this->setData(AddressInterface::KEY_SUFFIX, $suffix);

        return $this;
    }
}
