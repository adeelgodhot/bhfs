<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model\Subscription;

use Amasty\RecurringPayments\Api\Subscription\AddressInterface;
use Amasty\RecurringPayments\Model\Date;
use Magento\Directory\Model\CountryFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class GridSource
{
    /**
     * @var Date
     */
    private $date;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var CountryFactory
     */
    private $countryFactory;

    public function __construct(
        Date $date,
        PriceCurrencyInterface $priceCurrency,
        CountryFactory $countryFactory
    ) {

        $this->date = $date;
        $this->priceCurrency = $priceCurrency;
        $this->countryFactory = $countryFactory;
    }

    /**
     * @param int $timestamp
     * @return string
     */
    protected function formatDate(int $timestamp): string
    {
        return $this->date->convertDate(
            $this->date->convertFromUnix($timestamp),
            null,
            \IntlDateFormatter::MEDIUM,
            \IntlDateFormatter::MEDIUM
        );
    }

    /**
     * @param float $price
     * @param string $currency
     * @return string
     */
    protected function formatPrice(float $price, string $currency): string
    {
        return $this->priceCurrency->convertAndFormat(
            $price,
            false,
            null,
            null,
            mb_strtoupper($currency)
        );
    }

    /**
     * @param AddressInterface $address
     */
    protected function setCountry(AddressInterface $address)
    {
        try {
            $countryName = $this->countryFactory->create()->loadByCode(
                $address->getCountryId()
            )->getName();
        } catch (LocalizedException $exception) {
            $countryName = '';
        }

        $address->setCountry($countryName);
    }

    /**
     * @param AddressInterface $address
     */
    protected function setStreet(AddressInterface $address)
    {
        $street = $address->getStreet();

        if ($street && is_string($street)) {
            $address->setStreet(explode(PHP_EOL, $street));
        }
    }
}
