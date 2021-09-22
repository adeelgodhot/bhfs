<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Mpanel\Block\Product;

/**
 * Main contact form block
 */
 
use Magento\Catalog\Model\Product;
class ImageBuilder extends \Magento\Catalog\Block\Product\ImageBuilder
{
	/**
     * Product collection initialize process
     *
     * @return \Magento\Catalog\Block\Product\Image
     */
    public function create(Product $product = null, string $imageId = null, array $attributes = null)
    {
        /** @var \MGS\Mpanel\Helper\Data $themeHelper */
        /* $themeHelper =  \Magento\Framework\App\ObjectManager::getInstance()->get('MGS\Mpanel\Helper\Data');
        $imageSize = $themeHelper->getImageMinSize();
        

        $helper = $this->helperFactory->create()
            ->init($this->product, $this->imageId)->resize($imageSize['width'], $imageSize['height']);

        $template = $helper->getFrame()
            ? 'Magento_Catalog::product/image.phtml'
            : 'Magento_Catalog::product/image_with_borders.phtml';
        
        $data = [
            'data' => [
                'template' => $template,
                'image_url' => $helper->getUrl(),
                'width' => $imageSize['width'],
                'height' => $imageSize['height'],
                'label' => $helper->getLabel(),
                'ratio' =>  $this->getRatio($helper),
                'custom_attributes' => $this->getCustomAttributes(),
                'resized_image_width' => $imageSize['width'],
                'resized_image_height' => $imageSize['height'],
            ],
        ];

        return $this->imageFactory->create($data); */
		
		
		$product = $product ?? $this->product;
        $imageId = $imageId ?? $this->imageId;
        $attributes = $attributes ?? $this->attributes;
        return $this->imageFactory->create($product, $imageId, $attributes);
    }
    
}

