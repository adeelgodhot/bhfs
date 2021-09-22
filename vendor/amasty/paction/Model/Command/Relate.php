<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Paction
 */

declare(strict_types=1);

namespace Amasty\Paction\Model\Command;

use Amasty\Paction\Model\Command;
use Amasty\Paction\Model\ConfigProvider;
use Amasty\Paction\Model\EntityResolver;
use Amasty\Paction\Model\GetProductCollectionByIds;
use Amasty\Paction\Model\LinkActionsManagement;
use Amasty\Paction\Model\Source\Direction;
use Amasty\Paction\Model\Source\LinkType;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Link\SaveHandler;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

class Relate extends Command
{
    const TYPE = 'related';

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var SaveHandler
     */
    protected $saveProductLinks;

    /**
     * @var AdapterInterface
     */
    protected $connection;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var EntityResolver
     */
    private $entityResolver;

    /**
     * @var LinkActionsManagement
     */
    private $linkActionsManagement;

    /**
     * @var GetProductCollectionByIds
     */
    private $getProductCollectionByIds;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        SaveHandler $saveProductLinks,
        ResourceConnection $resource,
        ConfigProvider $configProvider,
        EntityResolver $entityResolver,
        LinkActionsManagement $linkActionsManagement,
        GetProductCollectionByIds $getProductCollectionByIds
    ) {
        $this->productRepository = $productRepository;
        $this->saveProductLinks = $saveProductLinks;
        $this->connection = $resource->getConnection();
        $this->configProvider = $configProvider;
        $this->entityResolver = $entityResolver;
        $this->linkActionsManagement = $linkActionsManagement;
        $this->getProductCollectionByIds = $getProductCollectionByIds;

        $this->type = self::TYPE;
        $this->info = [
            'confirm_title' => __('Relate')->render(),
            'confirm_message' => __('Are you sure you want to relate?')->render(),
            'type' => $this->type,
            'label' => __('Relate')->render(),
            'fieldLabel' => __('Selected To IDs')->render(),
            'placeholder' => __('id1,id2,id3')->render()
        ];
        $this->setFieldLabel();
    }

    public function execute(array $ids, int $storeId, string $val): Phrase
    {
        if (!$ids) {
            throw new LocalizedException(__('Please select product(s)'));
        }
        $vals = explode(',', $val);
        $num = 0;
        /** @var ProductInterface[] $mainProducts */
        $mainProducts = $this->getProductCollectionByIds->get(
            $ids,
            $this->entityResolver->getEntityLinkField(ProductInterface::class)
        );
        /** @var ProductInterface[] $targetProducts */
        $targetProducts = $this->getProductCollectionByIds->get($vals);
        $linkType = $this->linkActionsManagement->getLinkType($this->type);

        switch ($this->configProvider->getLinkType($this->type)) {
            case LinkType::MULTI_WAY:
                foreach ($mainProducts as $mainProduct) {
                    foreach ($mainProducts as $targetProduct) {
                        if ($mainProduct->getId() === $targetProduct->getId()) {
                            continue;
                        }
                        $this->linkActionsManagement->createNewLink($mainProduct, $targetProduct, $linkType);
                        $num++;
                    }
                }
                break;
            case LinkType::TWO_WAY:
                foreach ($targetProducts as $targetProduct) {
                    foreach ($mainProducts as $mainProduct) {
                        $this->linkActionsManagement->createNewLink($targetProduct, $mainProduct, $linkType);
                        $num++;
                        $this->linkActionsManagement->createNewLink($mainProduct, $targetProduct, $linkType);
                        $num++;
                    }
                }
                break;
            case LinkType::DEFAULT:
                foreach ($targetProducts as $targetProduct) {
                    foreach ($mainProducts as $mainProduct) {
                        if ($this->configProvider->getLinkDirection($this->type) == Direction::IDS_TO_SELECTED) {
                            $this->linkActionsManagement->createNewLink($mainProduct, $targetProduct, $linkType);
                        } else {
                            $this->linkActionsManagement->createNewLink($targetProduct, $mainProduct, $linkType);
                        }
                        $num++;
                    }
                }
                break;
        }

        if ($num === 1) {
            $success = __('Product association has been successfully added.');
        } else {
            $success = __('%1 product associations have been successfully added.', $num);
        }

        if (!$success && $this->configProvider->getLinkType($this->type) == LinkType::MULTI_WAY) {
            $this->errors[] = __('Please select more than 1 product');
        }

        return $success;
    }

    protected function setFieldLabel(): void
    {
        if ($this->configProvider->getLinkType($this->type) == LinkType::DEFAULT) {
            if ($this->configProvider->getLinkDirection($this->type) == Direction::IDS_TO_SELECTED) {
                $this->info['fieldLabel'] = 'IDs to Selected'; // new option
            } else {
                $this->info['fieldLabel'] = 'Selected To IDs'; // old option
            }
        } elseif ($this->configProvider->getLinkType($this->type) == LinkType::MULTI_WAY) {
            $this->info['fieldLabel'] = '';
            $this->info['hide_input'] = 1;
        }
    }
}
