<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Paction
 */

declare(strict_types=1);

namespace Amasty\Paction\Model\Command;

use Amasty\Paction\Model\Command;
use Amasty\Paction\Model\EntityResolver;
use Amasty\Paction\Model\GetProductCollectionByIds;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductCustomOptionRepositoryInterface;
use Magento\Framework\Phrase;

class Removeoptions extends Command
{
    const TYPE = 'removeoptions';

    /**
     * @var EntityResolver
     */
    private $entityResolver;

    /**
     * @var ProductCustomOptionRepositoryInterface
     */
    private $customOptionRepository;

    /**
     * @var GetProductCollectionByIds
     */
    private $getProductCollectionByIds;

    public function __construct(
        EntityResolver $entityResolver,
        ProductCustomOptionRepositoryInterface $customOptionRepository,
        GetProductCollectionByIds $getProductCollectionByIds
    ) {
        $this->entityResolver = $entityResolver;
        $this->customOptionRepository = $customOptionRepository;
        $this->getProductCollectionByIds = $getProductCollectionByIds;

        $this->type = self::TYPE;
        $this->info = [
            'confirm_title' => __('Remove Custom Options')->render(),
            'confirm_message' => __('Are you sure you want to remove custom options?')->render(),
            'type' => $this->type,
            'label' => __('Remove Custom Options')->render(),
            'fieldLabel' => ''
        ];
    }

    public function execute(array $ids, int $storeId, string $val): Phrase
    {
        $num = 0;
        $entityIdName = $this->entityResolver->getEntityLinkField(ProductInterface::class);

        foreach ($this->getProductCollectionByIds->get($ids, $entityIdName) as $product) {
            try {
                $options = $product->getOptions();

                if (empty($options)) {
                    continue;
                }
                foreach ($options as $option) {
                    $this->customOptionRepository->delete($option);
                }
                ++$num;
            } catch (\Exception $e) {
                $this->errors[] = __(
                    'Can not remove the options to the product ID=%1, the error is: %2',
                    $product->getId(),
                    $e->getMessage()
                );
            }
        }

        return __('Total of %1 products(s) have been successfully updated.', $num);
    }
}
