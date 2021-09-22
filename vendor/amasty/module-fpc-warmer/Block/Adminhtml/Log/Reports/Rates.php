<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Fpc
 */


namespace Amasty\Fpc\Block\Adminhtml\Log\Reports;

use Amasty\Fpc\Model\Queue\ProcessMetaInfo;
use Magento\Backend\Block\Template;

class Rates extends Template
{
    const LOG_PAGES = 'log_pages';

    protected $_template = 'Amasty_Fpc::log/rates.phtml';

    /**
     * @var \Amasty\Fpc\Mpdel\ResourceModel\Reports\CollectionFactory
     */
    private $reportsCollectionFactory;

    /**
     * @var \Amasty\Fpc\Model\Queue\ProcessMetaInfo
     */
    private $processStats;

    public function __construct(
        Template\Context $context,
        \Amasty\Fpc\Model\ResourceModel\Reports\CollectionFactory $reportsCollectionFactory,
        \Amasty\Fpc\Model\Queue\ProcessMetaInfo $processStats,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->reportsCollectionFactory = $reportsCollectionFactory;
        $this->processStats = $processStats;
    }

    /**
     * @return int
     */
    public function getHitsValue()
    {
        $hitsValue = $this->reportsCollectionFactory->create()
            ->getHitRate((int)$this->_scopeConfig->getValue('system/full_page_cache/ttl'));

        return $hitsValue;
    }

    /**
     * @return int
     */
    public function getCachedValue()
    {
        $totalPagesQueued = $this->processStats->getTotalPagesQueued();

        //to prevent divide by zero
        if ($totalPagesQueued !== 0) {
            $totalPagesCrawled = $this->processStats->getTotalPagesCrawled();
            $inCacheValue = $totalPagesCrawled / $totalPagesQueued * 100;

            return round($inCacheValue, 1);
        }

        return 0;
    }

    /**
     * @return int
     */
    public function getPendingValue()
    {
        $pendingValue = 100 - $this->getCachedValue();

        return round($pendingValue, 1);
    }

    /**
     * @return string
     */
    public function getCacheType()
    {
        $cacheType = $this->_scopeConfig->getValue('system/full_page_cache/caching_application');

        switch ($cacheType) {
            case \Magento\PageCache\Model\Config::BUILT_IN:
                return __('Built-in');
            case \Magento\PageCache\Model\Config::VARNISH:
                return __('Varnish');
            default:
                return __('Unknown');
        }
    }

    /**
     * @return string
     */
    public function getCacheTtl()
    {
        $cacheTTL = (int)$this->_scopeConfig->getValue('system/full_page_cache/ttl')  / 3600;

        return $cacheTTL . 'h';
    }
}
