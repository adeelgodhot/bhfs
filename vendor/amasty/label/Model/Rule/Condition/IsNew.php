<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Model\Rule\Condition;

use Amasty\Label\Model\ConfigProvider;
use Amasty\Label\Model\Source\Rules\Operator\BooleanOptions as IsNewOptionSource;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Phrase;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Rule\Model\Condition\Context;

class IsNew extends AbstractCondition
{
    /**
     * @var Yesno
     */
    private $yesNoOptionProvider;

    /**
     * @var IsNewOptionSource
     */
    private $isNewOperatorProvider;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    public function __construct(
        Context $context,
        Yesno $yesNoOptionProvider,
        ConfigProvider $configProvider,
        IsNewOptionSource $isNewOperatorProvider,
        TimezoneInterface $timezone,
        array $data = []
    ) {
        $this->yesNoOptionProvider = $yesNoOptionProvider;
        $this->isNewOperatorProvider = $isNewOperatorProvider;
        $this->configProvider = $configProvider;
        $this->timezone = $timezone;

        parent::__construct(
            $context,
            $data
        );
    }

    public function collectValidatedAttributes(ProductCollection $collection): void
    {
        $collection->addAttributeToSelect('news_from_date');
        $collection->addAttributeToSelect('news_to_date');
        $collection->addAttributeToSelect(ProductInterface::CREATED_AT);
    }

    public function validate(AbstractModel $model): bool
    {
        /** @var Product $model **/
        $isProductNew = $this->isProductNew($model);
        $requiredValue = (bool) $this->getValue();

        return $this->getOperator() === '==' ? ($isProductNew === $requiredValue) : ($isProductNew !== $requiredValue);
    }

    private function isProductNew(Product $product): bool
    {
        $isNew = false;
        $useFromToRanges = $this->configProvider->useNewFromToRanges();
        $fromDate = $product->getNewsFromDate();
        $toDate = $product->getNewsToDate();

        if ($useFromToRanges && ($fromDate !== null || $toDate !== null)) {
            $isNew = $this->isNewUsingRanges($product);
        } elseif ($this->configProvider->useCreationDateForNew()) {
            $isNew = $this->isNewUsingCreatedAt($product);
        }

        return $isNew;
    }

    private function isNewUsingRanges(Product $product): bool
    {
        $fromDate = $product->getNewsFromDate();
        $toDate = $product->getNewsToDate();
        $now = $this->timezone->date();
        $fromDate = $fromDate ? $this->timezone->date($fromDate) : null;
        $toDate = $toDate ? $this->timezone->date($toDate) : null;
        $isNotNew = ($fromDate !== null && $now < $fromDate) || ($toDate !== null && $now > $toDate);

        return !$isNotNew;
    }

    private function isNewUsingCreatedAt(Product $product): bool
    {
        $result = false;
        $createdAtDate = $product->getCreatedAt();

        if ($createdAtDate !== null) {
            $createdAtDate = $this->timezone->date($createdAtDate);
            $now = $this->timezone->date();
            $dateDiff = $now->diff($createdAtDate);
            $result = $dateDiff->days < $this->configProvider->getIsNewDaysThreshold();
        }

        return $result;
    }

    public function getAttributeElementHtml(): Phrase
    {
        return __('Is New');
    }

    public function getInputType(): string
    {
        return 'select';
    }

    public function getValueElementType(): string
    {
        return 'select';
    }

    public function getOperatorSelectOptions(): array
    {
        return $this->isNewOperatorProvider->toOptionArray();
    }

    public function getValueSelectOptions(): array
    {
        return $this->yesNoOptionProvider->toOptionArray();
    }
}
