<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_RulesLoyalty
 */


namespace Amasty\RulesLoyalty\Model;

use Amasty\Base\Model\ConfigProviderAbstract;

class ConfigProvider extends ConfigProviderAbstract
{
    /**
     * xpath prefix of module (section)
     */
    protected $pathPrefix = 'amrulesloyalty/';

    const GENERAL_BLOCK = 'general/';

    const HEADER = 'descr_header';
    const DESCRIPTION = 'description';
    const STATS_HEADER = 'stats_header';

    /**
     * @return string
     */
    public function getHeader()
    {
        return $this->getValue(self::GENERAL_BLOCK . self::HEADER);
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return (string)$this->getValue(self::GENERAL_BLOCK . self::DESCRIPTION);
    }

    /**
     * @return string
     */
    public function getStatsHeader()
    {
        return $this->getValue(self::GENERAL_BLOCK . self::STATS_HEADER);
    }
}
