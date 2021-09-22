<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */


namespace Amasty\Followup\Model;

use Magento\Framework\Pricing\PriceCurrencyInterface;

class Formatmanager extends \Magento\Framework\DataObject
{
    const TYPE_CUSTOMER = 'customer';
    const TYPE_CUSTOMER_NAME = 'customerName';
    const TYPE_CUSTOMER_GROUP = 'customer_group';
    const TYPE_CUSTOMER_GROUP_CODE = 'customerGroupCode';
    const TYPE_CUSTOMER_LOG = 'customer_log';
    const TYPE_HISTORY = 'history';
    const TYPE_HISTORY_COUPON_CODE = 'couponCode';
    const TYPE_ORDER = 'order';
    const TYPE_ORDER_STATUS = 'orderStatus';
    const TYPE_ORDER_INCREMENT_ID = 'orderIncrementId';
    const TYPE_ORDER_SHIPPING_METHOD = 'shippingMethod';
    const TYPE_QUOTE = 'quote';

    protected $config;
    protected $dateTime;
    protected $priceCurrency;

    public function init($config)
    {
        $this->config = $config;

        return $this;
    }

    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        $this->dateTime = $dateTime;
        $this->_date = $date;
        $this->priceCurrency = $priceCurrency;
    }

    public function formatDate($type, $field)
    {
        $ret = null;
        $object = isset($this->config[$type]) ? $this->config[$type] : null;

        if ($object) {
            if ($type == 'customer_log'
                || $type == 'customer'
            ) {
                switch ($field) {
                    case 'last_login_at':
                        $ret = $this->dateTime->formatDate($object->getLastLoginAt(), false);
                        break;
                    case 'created_at':
                        $ret = $object->getCreatedAt();
                        break;
                }
            } else {
                $ret = $this->dateTime->formatDate($object->getData($field), false);
            }
        }

        return $ret;
    }

    public function formatTime($type, $field)
    {

        $ret = null;
        $object = isset($this->config[$type]) ? $this->config[$type] : null;

        if ($object) {
            $ret = $this->dateTime->formatDate($object->getData($field), true);
        }

        return $ret;
    }

    public function formatPrice($type, $field)
    {
        if (isset($this->config[$type])) {
            $object = $this->config[$type];
            return $this->priceCurrency->format(
                $object->getData($field),
                true,
                PriceCurrencyInterface::DEFAULT_PRECISION,
                $object->getStore()
            );
        }
        return null;
    }
}
