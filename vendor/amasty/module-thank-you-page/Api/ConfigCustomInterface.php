<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ThankYouPage
 */

namespace Amasty\ThankYouPage\Api;

interface ConfigCustomInterface extends ConfigBasicInterface
{

    /**
     * @return string
     */
    public function getBlockTitle();

    /**
     * @return string
     */
    public function getBlockSubTitle();

    /**
     * @return string
     */
    public function getBlockText();

    /**
     * @return bool
     */
    public function isBlockUseCmsBlock();

    /**
     * @return string
     */
    public function getCmsBlockId();

    /**
     * @return string
     */
    public function getBackgroundImage();
}
