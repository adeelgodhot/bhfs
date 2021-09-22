<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Model\Label\Parts\FrontendSettings;

use Amasty\Label\Api\Data\LabelInterface;
use Amasty\Label\Api\Label\GetLabelImageUrlInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

class GetLabelImageUrl implements GetLabelImageUrlInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }

    public function execute(?string $imageName): ?string
    {
        $baseUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);

        return $imageName
            ? sprintf('%s%s/%s', $baseUrl, GetImageFilePath::AMASTY_LABEL_MEDIA_PATH, $imageName)
            : null;
    }

    public function getByLabel(LabelInterface $label): ?string
    {
        return $this->execute($label->getExtensionAttributes()->getFrontendSettings()->getImage());
    }
}
