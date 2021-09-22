<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ThankYouPage
 */

namespace Amasty\ThankYouPage\Model\Config\Backend;

class Image extends \Magento\Config\Model\Config\Backend\Image
{

    /**
     * @return string[]
     */
    protected function _getAllowedExtensions()
    {
        return ['png', 'jpg', 'jpe', 'jpeg', 'gif'];
    }
}
