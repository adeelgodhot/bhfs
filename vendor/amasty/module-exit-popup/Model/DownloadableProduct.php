<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ExitPopup
 */


namespace Amasty\ExitPopup\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;

class DownloadableProduct
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        EncryptorInterface $encryptor,
        UrlInterface $urlBuilder
    ) {
        $this->productRepository = $productRepository;
        $this->encryptor = $encryptor;
        $this->urlBuilder = $urlBuilder;
    }


    /**
     * @param int $productId
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getDownloadLink($productId)
    {
        if (!$productId) {
            return '';
        }

        /** @var Product $product */
        $product = $this->productRepository->getById($productId);
        $sku = urlencode($this->encryptor->encrypt($product->getSku()));

        return $this->urlBuilder->getUrl('exitpopup/productLink/download', ['sku' => $sku]);
    }
}
