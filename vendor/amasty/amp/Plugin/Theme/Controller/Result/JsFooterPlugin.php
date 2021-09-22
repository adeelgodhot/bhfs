<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Amp
 */


declare(strict_types=1);

namespace Amasty\Amp\Plugin\Theme\Controller\Result;

use Amasty\Amp\Model\ConfigProvider;
use Magento\Theme\Controller\Result\JsFooterPlugin as MagentoJsFooterPlugin;
use Magento\Framework\App\Response\Http;

class JsFooterPlugin
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(ConfigProvider $configProvider)
    {
        $this->configProvider = $configProvider;
    }

    /**
     * Prevent move js to page bottom in case of AMP page layout specifications
     *
     * @param MagentoJsFooterPlugin $subject
     * @param \Closure $proceed
     * @param Http $response
     * @return mixed|void
     */
    public function aroundBeforeSendResponse(
        MagentoJsFooterPlugin $subject,
        \Closure $proceed,
        Http $response
    ) {
        if ($this->configProvider->isAmpUrl()) {
            return;
        }

        return $proceed($response);
    }
}
