<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */


namespace Amasty\Followup\Model;

class SalesRule extends \Magento\SalesRule\Model\Rule
{
    /**
     * SalesRule constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\SalesRule\Model\CouponFactory $couponFactory
     * @param \Magento\SalesRule\Model\Coupon\CodegeneratorFactory $codegenFactory
     * @param \Magento\SalesRule\Model\Rule\Condition\CombineFactory $condCombineFactory
     * @param \Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory $condProdCombineF
     * @param \Magento\SalesRule\Model\ResourceModel\Coupon\Collection $couponCollection
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Amasty\Base\Model\Serializer $serializer
     * @param null $resource
     * @param null $resourceCollection
     * @param array $data
     * @param null $extensionFactory
     * @param null $customAttributeFactory
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\SalesRule\Model\CouponFactory $couponFactory,
        \Magento\SalesRule\Model\Coupon\CodegeneratorFactory $codegenFactory,
        \Magento\SalesRule\Model\Rule\Condition\CombineFactory $condCombineFactory,
        \Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory $condProdCombineF,
        \Magento\SalesRule\Model\ResourceModel\Coupon\Collection $couponCollection,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Amasty\Base\Model\Serializer $serializer,
        $resource = null,
        $resourceCollection = null,
        array $data = [],
        $extensionFactory = null,
        $customAttributeFactory = null
    ) {
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $localeDate,
            $couponFactory,
            $codegenFactory,
            $condCombineFactory,
            $condProdCombineF,
            $couponCollection,
            $storeManager,
            $resource,
            $resourceCollection,
            $data
        );
        $this->serializer = $serializer;
    }

    /**
     * _construct
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Amasty\Followup\Model\ResourceModel\Rule');
        $this->setIdFieldName('rule_id');
    }
}