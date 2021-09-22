<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ThankYouPage
 */

namespace Amasty\ThankYouPage\Model\Config\Types;

use Amasty\ThankYouPage\Api\ConfigBasicInterface;

class Basic extends \Amasty\Base\Model\ConfigProviderAbstract implements ConfigBasicInterface
{

    /**
     * xpath prefix of module (section)
     */
    protected $pathPrefix = 'amasty_thank_you_page/';

    /**
     * @var string
     */
    private $groupPrefix;

    /**#@+
     * xpath field parts
     */
    const FIELD_ENABLED = 'display';
    const FIELD_TITLE = 'title';
    const FIELD_SUB_TITLE = 'sub_title';
    const FIELD_TEXT = 'text';
    const FIELD_USE_CMS_BLOCK = 'use_cms_block';
    const FIELD_CMS_BLOCK = 'cms_block';

    /**#@-*/

    /**
     * @return bool
     */
    public function isBlockEnabled()
    {
        return $this->isSetFlag($this->getGroupPrefix() . self::FIELD_ENABLED);
    }

    /**
     * @return string
     */
    protected function getGroupPrefix()
    {
        return $this->groupPrefix . '/';
    }

    /**
     * @param string $groupPrefix
     *
     * @return Basic
     */
    public function setGroupPrefix($groupPrefix)
    {
        $this->groupPrefix = $groupPrefix;

        return $this;
    }
}
