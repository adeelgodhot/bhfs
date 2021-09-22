<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Model;

use Amasty\Label\Api\Data\LabelExtensionInterface;
use Amasty\Label\Api\Data\LabelInterface;
use Amasty\Label\Model\ResourceModel\Label as LabelResource;
use Magento\Framework\Api\ExtensionAttributesInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

class Label extends AbstractExtensibleModel implements LabelInterface, IdentityInterface
{
    const CACHE_TAG = 'amasty_label_entity';

    protected function _construct()
    {
        parent::_construct();
        $this->_cacheTag = self::CACHE_TAG;
        $this->_init(LabelResource::class);
    }

    public function getLabelId(): int
    {
        return (int) $this->_getData(self::LABEL_ID);
    }

    public function setLabelId(int $labelId): void
    {
        $this->setData(self::LABEL_ID, $labelId);
    }

    public function getName(): string
    {
        return (string) $this->_getData(self::NAME);
    }

    public function setName(string $name): void
    {
        $this->setData(self::NAME, $name);
    }

    public function getStatus(): int
    {
        return (int) $this->_getData(self::STATUS);
    }

    public function setStatus(int $status): void
    {
        $this->setData(self::STATUS, $status);
    }

    public function getPriority(): int
    {
        return (int) $this->_getData(self::PRIORITY);
    }

    public function setPriority(int $priority): void
    {
        $this->setData(self::PRIORITY, $priority);
    }

    public function getIsSingle(): bool
    {
        return (bool) $this->_getData(self::IS_SINGLE);
    }

    public function setIsSingle(bool $isSingle): void
    {
        $this->setData(self::IS_SINGLE, $isSingle);
    }

    public function getUseForParent(): bool
    {
        return (bool) $this->_getData(self::USE_FOR_PARENT);
    }

    public function setUseForParent(bool $useForParent): void
    {
        $this->setData(self::USE_FOR_PARENT, $useForParent);
    }

    public function getConditionSerialized(): string
    {
        return (string) $this->_getData(self::CONDITION_SERIALIZED) ?: '{}';
    }

    public function setConditionSerialized(string $conditionSerialized): void
    {
        $this->setData(self::CONDITION_SERIALIZED, $conditionSerialized);
    }

    public function getActiveFrom(): ?string
    {
        $activeFrom = $this->_getData(self::ACTIVE_FROM);

        return is_string($activeFrom) ? $activeFrom : null;
    }

    public function setActiveFrom(?string $activeFrom): void
    {
        $this->setData(self::ACTIVE_FROM, $activeFrom);
    }

    public function getActiveTo(): ?string
    {
        $activeTo = $this->_getData(self::ACTIVE_TO);

        return is_string($activeTo) ? $activeTo : null;
    }

    public function setActiveTo(?string $activeTo): void
    {
        $this->setData(self::ACTIVE_TO, $activeTo);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return LabelExtensionInterface|ExtensionAttributesInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    public function getIdentities(): array
    {
        return [self::CACHE_TAG, sprintf('%s_%d', self::CACHE_TAG, $this->getLabelId())];
    }

    public function setExtensionAttributes(LabelExtensionInterface $extensionAttributes): void
    {
        $this->setData(self::EXTENSION_ATTRIBUTES_KEY, $extensionAttributes);
    }
}
