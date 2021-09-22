<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


namespace Amasty\Label\Plugin\Catalog\Indexer\Product;

use Magento\Catalog\Model\Indexer\Product\Price as PriceIndexer;
use Amasty\Label\Model\Indexer\LabelIndexer;
use Magento\Catalog\Cron\RefreshSpecialPrices;

class Price
{
    /**
     * @var array
     */
    private $ids;

    /**
     * @var bool
     */
    private $needReindex = false;

    /**
     * @var LabelIndexer
     */
    private $labelIndexer;

    public function __construct(
        LabelIndexer $labelIndexer
    ) {
        $this->labelIndexer = $labelIndexer;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param PriceIndexer $subject
     * @param array $ids
     *
     * @return array
     */
    public function beforeExecuteList(PriceIndexer $subject, $ids)
    {
        $this->ids = $ids;

        return [$ids];
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param PriceIndexer $subject
     */
    public function afterExecuteList(
        PriceIndexer $subject
    ) {
        if ($this->needReindex) {
            $this->labelIndexer->execute($this->ids);
        }
        $this->ids = [];
    }

    /**
     * @param $subject
     * @param array $ids
     *
     * @return array
     */
    public function beforeExecute($subject, $ids = [])
    {
        if ($subject instanceof RefreshSpecialPrices) {
            $this->needReindex = true;
        }

        return [$ids];
    }
}
