<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedTools
 */

declare(strict_types=1);

namespace Amasty\PageSpeedTools\Model\Output;

class AmpRequestChecker
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    public function __construct(\Magento\Framework\App\RequestInterface $request)
    {
        $this->request = $request;
    }

    public function check(): bool
    {
        if (strpos($this->request->getOriginalPathInfo(), '/amp/') !== false
            || $this->request->getParam('amp')
        ) {
            return true;
        }

        return false;
    }
}
