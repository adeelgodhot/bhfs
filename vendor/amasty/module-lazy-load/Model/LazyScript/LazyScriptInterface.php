<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_LazyLoad
 */

declare(strict_types=1);

namespace Amasty\LazyLoad\Model\LazyScript;

interface LazyScriptInterface
{
    public function getName(): string;

    public function getType(): string;

    public function getCode(): string;
}
