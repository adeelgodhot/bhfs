<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Paction
 */

declare(strict_types=1);

namespace Amasty\Paction\Block\Adminhtml\Product\Edit\Action\Attribute\Tab\TierPrice;

use Magento\Framework\Serialize\Serializer;
use Amasty\Paction\Model\Source\TierPrice as AmTierPrice;
use Magento\Backend\Block\Template\Context;
use Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Price\Group\AbstractGroup;
use Magento\Catalog\Model\Config\Source\Product\Options\TierPrice;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Directory\Helper\Data;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Framework\Module\Manager;
use Magento\Framework\Registry;

class Group extends AbstractGroup
{
    /**
     * @var TierPrice
     */
    private $tierPriceValueType;

    /**
     * @var Serializer\Json
     */
    private $serializer;

    public function __construct(
        Context $context,
        GroupRepositoryInterface $groupRepository,
        Data $directoryHelper,
        Manager $moduleManager,
        Registry $registry,
        Serializer\Json $serializer,
        GroupManagementInterface $groupManagement,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CurrencyInterface $localeCurrency,
        AmTierPrice $tierPriceValueType,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $groupRepository,
            $directoryHelper,
            $moduleManager,
            $registry,
            $groupManagement,
            $searchCriteriaBuilder,
            $localeCurrency,
            $data
        );
        $this->tierPriceValueType = $tierPriceValueType;
        $this->serializer = $serializer;
    }

    public function isScopeGlobal(): bool
    {
        return true;
    }

    public function getPriceValueTypesJson(): string
    {
        return $this->serializer->serialize($this->tierPriceValueType->toOptionArray());
    }

    public function getGroupsJson(): string
    {
        $allGroupId = $this->getAllGroupsId();
        $groups = array_replace_recursive($allGroupId, $this->getCustomerGroups());

        return $this->serializer->serialize($groups);
    }

    public function getWebsitesJson(): string
    {
        return $this->serializer->serialize($this::getWebsites());
    }

    public function getApplyToJson(): string
    {
        $element = $this->getElement();
        $applyTo = $element->hasEntityAttribute()
            ? $element->getEntityAttribute()->getApplyTo()
            : [];

        return $this->serializer->serialize($applyTo);
    }
}
