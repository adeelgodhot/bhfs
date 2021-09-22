<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ThankYouPage
 */

namespace Amasty\ThankYouPage\Api;

interface ConfigNewsletterInterface extends ConfigBasicInterface
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
    public function getConfirmationMessage();

    /**
     * @return string
     */
    public function getAlreadySubscribedText();
}
