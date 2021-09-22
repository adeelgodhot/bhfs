<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Fpc
 */


namespace Amasty\Fpc\Model\Debug;

use Magento\Framework\Model\AbstractModel;

class DebugContext extends AbstractModel
{
    const URL = 'url';
    const CONTEXT_DATA = 'context_data';

    protected function _construct()
    {
        $this->_init(\Amasty\Fpc\Model\ResourceModel\DebugContext::class);
    }

    public function setUrl(string $url)
    {
        $this->setData(self::URL, $url);

        return $this;
    }

    public function getUrl(): string
    {
        return (string)$this->_getData(self::URL);
    }

    public function setContextData(string $contextJson)
    {
        $this->setData(self::CONTEXT_DATA, $contextJson);

        return $this;
    }

    public function getContextDataJson(): string
    {
        return (string)$this->_getData(self::CONTEXT_DATA);
    }
}
