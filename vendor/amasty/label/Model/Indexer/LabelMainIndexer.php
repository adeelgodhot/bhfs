<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


namespace Amasty\Label\Model\Indexer;

use Magento\Framework\Exception\LocalizedException;

class LabelMainIndexer extends LabelIndexer
{
    const INDEXER_ID = 'amasty_label_main';

    /**
     * Execute materialization on ids entities
     *
     * @param int[] $ids
     * @throws LocalizedException
     */
    public function execute($ids)
    {
        $this->executeByLabelIds($ids);
    }

    /**
     * @param int[] $ids
     * @throws LocalizedException
     */
    public function executeList(array $ids)
    {
        $this->executeByLabelIds($ids);
    }
}
