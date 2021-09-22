<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Fpc
 */


declare(strict_types=1);

namespace Amasty\Fpc\Model\Crawler\Request;

use Amasty\Fpc\Helper\Http as HttpHelper;
use Amasty\Fpc\Model\Config;
use Amasty\Fpc\Model\Crawler\RegistryConstants;
use GuzzleHttp\RequestOptions;

class DefaultParamProvider
{
    /**
     * @var Config
     */
    private $configProvider;

    private $defaultParams = null;

    public function __construct(Config $configProvider)
    {
        $this->configProvider = $configProvider;
    }

    public function getDefaultParams()
    {
        if ($this->defaultParams === null) {
            $requestParams = [];

            if ($this->configProvider->isHttpAuth()) {
                $httpLogin = trim($this->configProvider->getLogin());
                $httpPassword = trim($this->configProvider->getPassword());

                if ($httpLogin && $httpPassword) {
                    $requestParams[RequestOptions::AUTH] = [$httpLogin, $httpPassword];
                }
            }

            if ($this->configProvider->isSkipVerification()) {
                $requestParams[RequestOptions::VERIFY] = false;
            }

            if ($delay = $this->configProvider->getDelay()) {
                $requestParams[RequestOptions::DELAY] = $delay;
            }

            $requestParams[RequestOptions::COOKIES] = [
                RegistryConstants::CRAWLER_SESSION_COOKIE_NAME => RegistryConstants::CRAWLER_SESSION_COOKIE_VALUE
            ];
            $requestParams[RequestOptions::HEADERS] = [
                HttpHelper::STATUS_HEADER => 'crawl',
                'User-Agent' => RegistryConstants::CRAWLER_USER_AGENT
            ];
            $this->defaultParams = $requestParams;
        }

        return $this->defaultParams;
    }
}
