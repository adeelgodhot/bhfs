<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */

namespace Amasty\Followup\Cron;

class RefreshHistory
{
    protected $_indexerFactory;

    public function __construct(
        \Amasty\Followup\Model\IndexerFactory $indexerFactory
    ){
        $this->_indexerFactory = $indexerFactory;
    }

    public function execute()
    {
        $this->_indexerFactory->create()->run();
    }
}