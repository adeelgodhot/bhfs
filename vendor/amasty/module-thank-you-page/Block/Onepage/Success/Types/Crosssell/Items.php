<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ThankYouPage
 */

namespace Amasty\ThankYouPage\Block\Onepage\Success\Types\Crosssell;

use Amasty\ThankYouPage\Model\Config;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\Product\LinkFactory;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Checkout\Block\Cart\Crosssell as CartCrosssell;
use Magento\CatalogInventory\Helper\Stock as StockHelper;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Quote\Model\Quote\Item\RelatedProducts;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory as QuoteCollectionFactory;
use Magento\Quote\Model\ResourceModel\Quote\Collection;
use Magento\Quote\Model\Quote;
use Magento\Framework\Api\SortOrder;
use Magento\Sales\Model\Order as SalesOrder;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;

/**
 * Crosssell block
 */
class Items extends CartCrosssell
{
    const AMASTY_THANKYOUPAGE_SHOW_OOS = 'AMASTY_THANKYOUPAGE_SHOW_OOS';

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var QuoteCollectionFactory
     */
    private $quoteCollectionFactory;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var OrderCollectionFactory
     */
    private $orderCollectionFactory;

    public function __construct(
        Context $context,
        Session $checkoutSession,
        Visibility $productVisibility,
        LinkFactory $productLinkFactory,
        RelatedProducts $itemRelationsList,
        CartRepositoryInterface $quoteRepository,
        StockHelper $stockHelper,
        DataPersistorInterface $dataPersistor,
        QuoteCollectionFactory $quoteCollectionFactory,
        Config $config,
        OrderCollectionFactory $orderCollectionFactory,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $checkoutSession,
            $productVisibility,
            $productLinkFactory,
            $itemRelationsList,
            $stockHelper,
            $data
        );
        $this->quoteRepository = $quoteRepository;
        $this->dataPersistor = $dataPersistor;
        $this->quoteCollectionFactory = $quoteCollectionFactory;
        $this->config = $config;
        $this->orderCollectionFactory = $orderCollectionFactory;
    }

    /**
     * Get quote instance
     *
     * @return \Magento\Quote\Api\Data\CartInterface
     * @codeCoverageIgnore
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getQuote()
    {
        try {
            return $this->quoteRepository->get($this->getQuoteId());
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $lastQuote = $this->getLastQuote();

            if ($this->getQuoteId() != $lastQuote->getId()) {
                return $this->quoteRepository->get($lastQuote->getId());
            }

            throw $e;
        }
    }

    /**
     * @param int|null $limit
     *
     * @return $this
     */
    public function setProductLimit($limit)
    {
        if (null !== $limit) {
            $this->_maxItemCount = $limit;
        }

        return $this;
    }

    /**
     * Get crosssell products collection. Setting dataPersistor flag to avoid filtering Out Of Stock items
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection
     */
    protected function _getCollection()
    {
        if ($this->getShowOutOfStock()) {
            $this->dataPersistor->set(self::AMASTY_THANKYOUPAGE_SHOW_OOS, true);
        }

        $collection = parent::_getCollection();

        return $collection;
    }

    /**
     * @return int
     */
    private function getQuoteId()
    {
        $quote = $this->getLastQuote();
        $quoteId = $quote->getId();
        $incrementId = $this->config->getOrderIncrementId();

        if ($incrementId) {
            $incrementId = str_replace('#', '', $incrementId);
            $order = $this->getOrderByIncrementId($incrementId);

            if ($order->getQuoteId()) {
                $quoteId = $order->getQuoteId();
            }
        }

        return $quoteId;
    }

    /**
     * @return Quote
     */
    private function getLastQuote()
    {
        /** @var Collection $orderCollection */
        $quoteCollection = $this->quoteCollectionFactory->create();
        $quoteCollection
            ->addFieldToFilter('is_active', 0)
            ->setPageSize(1)
            ->setOrder('entity_id', SortOrder::SORT_DESC);

        return $quoteCollection->getLastItem();
    }

    /**
     * @param int $incrementId
     *
     * @return SalesOrder
     */
    private function getOrderByIncrementId($incrementId)
    {
        $orderCollection = $this->orderCollectionFactory->create();
        $orderCollection->addFieldToFilter('increment_id', $incrementId)
            ->setPageSize(1)
            ->setCurPage(1);

        return $orderCollection->getLastItem();
    }
}
