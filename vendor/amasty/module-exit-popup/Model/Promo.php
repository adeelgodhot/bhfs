<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ExitPopup
 */


namespace Amasty\ExitPopup\Model;

use Magento\SalesRule\Api\Data\CouponGenerationSpecInterfaceFactory;
use Magento\SalesRule\Helper\Coupon;
use Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory;
use Magento\SalesRule\Model\Service\CouponManagementService;

class Promo
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var CouponGenerationSpecInterfaceFactory
     */
    private $couponGenerationSpecInterfaceFactory;

    /**
     * @var Coupon
     */
    private $couponHelper;

    /**
     * @var CouponManagementService
     */
    private $couponManagementService;

    public function __construct(
        CollectionFactory $collectionFactory,
        CouponGenerationSpecInterfaceFactory $couponGenerationSpecInterfaceFactory,
        Coupon $couponHelper,
        CouponManagementService $couponManagementService
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->couponGenerationSpecInterfaceFactory = $couponGenerationSpecInterfaceFactory;
        $this->couponHelper = $couponHelper;
        $this->couponManagementService = $couponManagementService;
    }

    /**
     * @param int $ruleId
     *
     * @return string
     */
    public function getPromoCodeByRuleId($ruleId)
    {
        $couponCode = '';

        if (!$ruleId) {
            return $couponCode;
        }

        try {
            $couponSpec =
                $this->couponGenerationSpecInterfaceFactory->create(
                    [
                        'data' => [
                            'rule_id' => $ruleId,
                            'quantity' => 1,
                            'length' => $this->couponHelper->getDefaultLength(),
                            'prefix' => $this->couponHelper->getDefaultPrefix(),
                            'suffix' => $this->couponHelper->getDefaultSuffix(),
                            'dash' => $this->couponHelper->getDefaultDashInterval(),
                        ],
                    ]
                );
            $couponCodes = $this->couponManagementService->generate($couponSpec);
        } catch (\Exception $e) {
            return '';
        }

        if (count($couponCodes)) {
            $couponCode = $couponCodes[0];
        }

        return $couponCode;
    }
}
