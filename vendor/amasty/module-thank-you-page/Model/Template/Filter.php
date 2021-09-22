<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ThankYouPage
 */

namespace Amasty\ThankYouPage\Model\Template;

use Amasty\ThankYouPage\Model\Config;
use Magento\Framework\Stdlib\StringUtils;
use Magento\SalesRule\Api\Data\CouponGenerationSpecInterfaceFactory;
use Magento\SalesRule\Helper\Coupon;
use Magento\Framework\Filter\Template as OriginFilter;
use Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Service\CouponManagementService;

class Filter extends OriginFilter
{

    /**
     * @var string
     */
    private $couponCode;

    /**
     * @var \Amasty\ThankYouPage\Model\Config
     */
    private $config;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var CouponGenerationSpecInterfaceFactory
     */
    private $couponGenerationSpecInterfaceFactory;

    /**
     * @var CouponManagementService
     */
    private $couponManagementService;

    /**
     * @var Coupon
     */
    private $couponHelper;

    public function __construct(
        StringUtils $string,
        Config $config,
        CouponGenerationSpecInterfaceFactory $couponGenerationSpecInterfaceFactory,
        CouponManagementService $couponManagementService,
        CollectionFactory $collectionFactory,
        Coupon $couponHelper,
        $variables = []
    ) {
        parent::__construct($string, $variables);
        $this->config = $config;
        $this->couponGenerationSpecInterfaceFactory = $couponGenerationSpecInterfaceFactory;
        $this->couponManagementService = $couponManagementService;
        $this->couponHelper = $couponHelper;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Trans directive for coupon support
     *
     * Usage:
     *
     *   {{coupon}}
     *
     * @param string[] $construction
     *
     * @return string
     */
    public function couponDirective($construction)
    {
        return $this->getCouponCode();
    }

    /**
     * @return string|null
     */
    private function getCouponCode()
    {
        if (null !== $this->couponCode) {
            return $this->couponCode;
        }

        $this->couponCode = false;
        $ruleId = $this->config->getCouponRuleId();
        if (!$ruleId) {
            return null;
        }

        /** @var Rule $rule */
        /**
         * Can't use RuleFactory as need to obtain Magento\SalesRule\Model\Rule which is possible through
         * collection only
         */
        $rule = $this->collectionFactory->create()
            ->addFieldToFilter('rule_id', $ruleId)
            ->addFieldToFilter('is_active', 1)
            ->load()
            ->getFirstItem();

        if (!$rule->getRuleId()) {
            return $this->couponCode;
        }

        try {
            $couponSpec =
                $this->couponGenerationSpecInterfaceFactory->create([
                    'data' => [
                        'rule_id'  => $ruleId,
                        'quantity' => 1,
                        'length'   => $this->couponHelper->getDefaultLength(),
                        'prefix'   => $this->couponHelper->getDefaultPrefix(),
                        'suffix'   => $this->couponHelper->getDefaultSuffix(),
                        'dash'     => $this->couponHelper->getDefaultDashInterval(),
                    ],
                ]);
            $couponCodes = $this->couponManagementService->generate($couponSpec);
        } catch (\Exception $e) {
            return $this->couponCode;
        }

        if (count($couponCodes)) {
            $this->couponCode = $couponCodes[0];
        }

        return $this->couponCode;
    }
}