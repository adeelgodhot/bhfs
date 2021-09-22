<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Fpc
 */


declare(strict_types=1);

namespace Amasty\Fpc\Model\Crawler;

class RegistryConstants
{
    const CRAWLER_AGENT_EXTENSION = 'Amasty_Fpc';
    const CRAWLER_SESSION_COOKIE_NAME = 'PHPSESSID';
    const CRAWLER_SESSION_COOKIE_VALUE = 'amasty-fpc-crawler';
    const CRAWLER_URL_HEADER = 'X-Amasty-Crawler-Url';
    const CRAWLER_USER_AGENT = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) '
        . 'Chrome/58.0.3029.110 Safari/537.36';
}
