<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


namespace Amasty\Label\Plugin\Product\View\Type;

use Amasty\Label\Model\ConfigProvider;
use Magento\ConfigurableProduct\Block\Product\View\Type\Configurable as TypeConfigurable;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Json\Decoder;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\UrlInterface;

class Configurable
{
    const LABEL_RELOAD = 'amasty_label/ajax/label';

    /**
     * @var Decoder
     */
    private $jsonDecoder;

    /**
     * @var EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        Decoder $jsonDecoder,
        EncoderInterface $jsonEncoder,
        UrlInterface $urlBuilder,
        RequestInterface $request,
        ConfigProvider $configProvider
    ) {
        $this->jsonDecoder = $jsonDecoder;
        $this->jsonEncoder = $jsonEncoder;
        $this->urlBuilder = $urlBuilder;
        $this->request = $request;
        $this->configProvider = $configProvider;
    }

    /**
     * @param TypeConfigurable $subject
     * @param $result
     * @return string
     */
    public function afterGetJsonConfig(
        TypeConfigurable $subject,
        $result
    ) {
        $result = $this->jsonDecoder->decode($result);

        $result['label_reload'] = $this->getReloadUrl();
        $result['label_category'] = $this->configProvider->getProductListContainerPath();
        $result['label_product'] = $this->configProvider->getProductContainerPath();
        $result['original_product_id'] = $subject->getProduct()->getId();

        return $this->jsonEncoder->encode($result);
    }

    /**
     * @return string
     */
    private function getReloadUrl()
    {
        return $this->urlBuilder->getUrl(
            self::LABEL_RELOAD,
            [
                '_secure' => $this->request->isSecure()
            ]
        );
    }
}
