<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


namespace Amasty\Label\Model;

use Amasty\Base\Model\Serializer;
use Amasty\Label\Api\Data\RenderSettingsInterface;
use Amasty\Label\Api\Data\RenderSettingsInterfaceFactory;
use Amasty\Label\Api\LabelRepositoryInterface;
use Amasty\Label\Block\Label as LabelBlock;
use Amasty\Label\Model\ResourceModel\GetLabelsByProductIdAndStoreId;
use Amasty\Label\Model\ResourceModel\Label\Collection;
use Amasty\Label\Model\ResourceModel\Label\GetRelatedEntitiesIds as GetLabelCustomerGroupIds;
use Amasty\Label\Model\Source\Status;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Profiler;
use Magento\Framework\View\LayoutInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LabelViewer
{
    const MODE_CATEGORY = 'category';

    const MODE_PRODUCT_PAGE = 'product';

    /**
     * @var int|null
     */
    private $maxLabelCount = null;

    /**
     * @var Configurable
     */
    private $productTypeConfigurable;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * @var GetLabelCustomerGroupIds
     */
    private $getLabelCustomerGroupIds;

    /**
     * @var SessionFactory
     */
    private $customerSessionFactory;

    /**
     * @var RenderSettingsInterfaceFactory
     */
    private $renderSettingsFactory;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var LabelRepositoryInterface
     */
    private $labelRepository;

    /**
     * @var GetLabelsByProductIdAndStoreId
     */
    private $getLabelsByProductIdAndStoreId;

    public function __construct(
        LayoutInterface $layout,
        Configurable $catalogProductTypeConfigurable,
        Serializer $serializer,
        SessionFactory $customerSessionFactory,
        GetLabelCustomerGroupIds $getLabelCustomerGroupIds,
        RenderSettingsInterfaceFactory $renderSettingsFactory,
        ConfigProvider $configProvider,
        LabelRepositoryInterface $labelRepository,
        GetLabelsByProductIdAndStoreId $getLabelsByProductIdAndStoreId
    ) {
        $this->productTypeConfigurable = $catalogProductTypeConfigurable;
        $this->serializer = $serializer;
        $this->layout = $layout;
        $this->getLabelCustomerGroupIds = $getLabelCustomerGroupIds;
        $this->customerSessionFactory = $customerSessionFactory;
        $this->renderSettingsFactory = $renderSettingsFactory;
        $this->configProvider = $configProvider;
        $this->labelRepository = $labelRepository;
        $this->getLabelsByProductIdAndStoreId = $getLabelsByProductIdAndStoreId;
    }

    /**
     * @param Product $product
     * @param int $mode
     * @param bool $shouldMove
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function renderProductLabel(Product $product, int $mode = Collection::MODE_LIST, bool $shouldMove = false)
    {
        $html = '';

        Profiler::start('__RenderAmastyProductLabel__');

        foreach ($this->getAppliedLabels($product, $shouldMove, $mode) as $appliedLabel) {
            $html .= $this->generateHtml($appliedLabel);
        }

        Profiler::stop('__RenderAmastyProductLabel__');

        return $html;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     *
     * @param Product $product
     * @param bool $shouldMove
     * @param int $mode
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAppliedLabels(Product $product, $shouldMove = false, int $mode = Collection::MODE_LIST)
    {
        $appliedItems = [];
        $appliedLabelIds = [];
        $applied = 0;
        $maxLabelCount = $this->getMaxLabelCount();
        $outOfStockConfig = $this->checkOutOfStockLabel($mode, $product);

        if ($outOfStockConfig !== null) {
            $appliedItems = $outOfStockConfig;
        } else {
            $productLabels = $this->getLabelsByProductIdAndStoreId->execute(
                (int) $product->getId(),
                $product->getStoreId(),
                $mode
            );

            foreach ($productLabels as $label) {
                if ($applied >= $maxLabelCount) {
                    break;
                }

                if (!$this->isCustomerGroupLabelAllowed($label)) {
                    continue;
                }

                $label->setShouldMove($shouldMove);

                if ($this->addLabelToApplied($label, $appliedLabelIds)) {
                    $this->populateRenderSettings($label, $product);
                    $applied++;
                    $appliedItems[] = $label;
                }
            }
        }

        return $appliedItems;
    }

    private function checkOutOfStockLabel(int $mode, ProductInterface $product): ?array
    {
        $result = null;
        $outOfStockLabelId = $this->configProvider->getDefaultOutOfStockLabelId();

        if ($this->configProvider->isOutOfStockLabelEnabled()
            && $outOfStockLabelId
            && !$product->isSalable()
        ) {
            try {
                $outOfStockLabel = $this->labelRepository->getById($outOfStockLabelId, $mode);

                if ($outOfStockLabel->getStatus() === Status::ACTIVE) {
                    $this->populateRenderSettings($outOfStockLabel, $product);
                    $result = [$outOfStockLabelId => $outOfStockLabel];
                }
            } catch (NoSuchEntityException $exception) {
                null; // if saved label id is invalid - do nothing
            }
        }

        return $result;
    }

    private function populateRenderSettings(Label $label, ProductInterface $product): void
    {
        /** @var RenderSettingsInterface $renderSettings **/
        $renderSettings = $this->renderSettingsFactory->create();
        $renderSettings->setProduct($product);
        $label->getExtensionAttributes()->setRenderSettings($renderSettings);
    }

    /**
     * @param \Amasty\Label\Model\Label $label
     * @param $appliedLabelIds
     *
     * @return bool
     */
    private function addLabelToApplied(Label $label, &$appliedLabelIds)
    {
        $position = $label->getExtensionAttributes()->getFrontendSettings()->getPosition();

        if (!$this->isShowSeveralLabels()) {
            if (array_search($position, $appliedLabelIds) !== false) {
                return false;
            }
        }

        if ($label->getIsSingle() && count($appliedLabelIds) > 0) {
            return false;
        }

        $appliedLabelIds[$label->getId()] = $position;

        return true;
    }

    private function isCustomerGroupLabelAllowed(Label $label): bool
    {
        $labelCustomerGroups = $this->getLabelCustomerGroupIds->execute($label->getLabelId());
        $customerGroups = [$this->getCustomerSession()->getCustomerGroupId(), GroupInterface::CUST_GROUP_ALL];

        return count(array_intersect($labelCustomerGroups, $customerGroups)) > 0;
    }

    /**
     * generate block with label configuration
     * @param Label $label
     * @return string
     */
    private function generateHtml(Label $label)
    {
        $block = $this->layout->createBlock(LabelBlock::class);
        $block->setLabel($label);

        return $block->toHtml();
    }

    /**
     * @return bool
     */
    private function isShowSeveralLabels(): bool
    {
        return $this->configProvider->isShowSeveralOnPlace();
    }

    /**
     * @return int
     */
    private function getMaxLabelCount()
    {
        if ($this->maxLabelCount === null) {
            $this->maxLabelCount = $this->configProvider->getMaxLabels();
        }

        return $this->maxLabelCount;
    }

    private function getCustomerSession(): Session
    {
        return $this->customerSessionFactory->create();
    }
}
