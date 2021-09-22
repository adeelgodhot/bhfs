<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingCalculator
 */


namespace Amasty\ShippingCalculator\Block;

use Amasty\ShippingCalculator\Model\ConfigProvider;
use Magento\Framework\View\Element\Template;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class EstimateResult extends Template
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * EstimateResult constructor.
     * @param Context $context
     * @param Registry $registry
     * @param ConfigProvider $configProvider
     * @param PriceCurrencyInterface $priceCurrency
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ConfigProvider $configProvider,
        PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry = $registry;
        $this->configProvider = $configProvider;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * @return \Magento\Quote\Api\Data\ShippingMethodInterface[]
     */
    public function getShippingRates()
    {
        return $this->registry->registry('amasty_shipping_rates');
    }

    /**
     * @return string
     */
    public function getNotFoundMessage()
    {
        return $this->configProvider->getNotFoundMessage();
    }

    /**
     * @param float $amount
     * @param bool $includeContainer
     * @return float
     */
    public function formatPrice($amount, $includeContainer = true)
    {
        return $this->priceCurrency->format($amount, $includeContainer);
    }

}
