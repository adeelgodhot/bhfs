<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */


namespace Amasty\Followup\Block\Email;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\ObjectManagerInterface;

class Items extends \Magento\Framework\View\Element\Template
{
    protected $_params = [
        'mode' => [
            'default' => 'table',
            'available' => [
                'list', 'table'
            ]
        ],
        'image' => [
            'default' => 'yes',
            'available' => [
                'yes', 'no'
            ]
        ],
        'price' => [
            'default' => 'yes',
            'available' => [
                'yes', 'no'
            ]
        ],
        'priceFormat' => [
            'default' => 'exculdeTax',
            'available' => [
                'exculdeTax', 'includeTax'
            ]
        ],
        'descriptionFormat' => [
            'default' => 'short',
            'available' => [
                'short', 'full', 'no'
            ]
        ],
        'discount' => [
            'default' => 'yes',
            'available' => [
                'yes', 'no'
            ]
        ],
        'showSpecialPrice' => [
            'default' => 'no',
            'available' => [
                'yes', 'no'
            ]
        ],
    ];

    protected $priceCurrency;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Amasty\Followup\Model\Urlmanager
     */
    protected $_urlManager;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $_imageHelper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        PriceCurrencyInterface $priceCurrency,
        ObjectManagerInterface $objectManager,
        \Amasty\Followup\Model\Urlmanager $urlManager,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->priceCurrency = $priceCurrency;
        $this->objectManager = $objectManager;
        $this->_urlManager = $urlManager;
        $this->_imageHelper = $imageHelper;
        $this->dateTime = $dateTime;
        $this->productRepository = $productRepository;
    }

    public function setMode($mode)
    {
        $this->setTemplate('email/' . $mode . '.phtml');
        return parent::setMode($mode);
    }

    public function getMode()
    {
        return $this->_getLayoutParam('mode');
    }

    /**
     * @return array
     */
    public function getItems()
    {
        $items = [];
        if ($this->getQuote()) {
            $childBlock = $this->getChildBlock('amfollowup.items.data');
            $childBlock->setQuote($this->getQuote());
            $items = $childBlock->getItems();
        }

        return $items;
    }

    public function getProductPrice($product)
    {
        if ($this->showPriceIncTax()) {
            $price = $product->getPriceInclTax();
        } else {
            $price = $product->getPrice();
            if (!$price) {
                $price = $product->getFinalPrice();
            }
        }

        return $price;
    }

    public function getFormatedProductPrice($price)
    {
        return $this->formatPrice($price);
    }

    public function formatPrice($price)
    {
        $store = null;
        if ($this->getQuote()) {
            $store = $this->getQuote()->getStore();
        } elseif ($this->getCustomer() && $this->getCustomer()->getStore()) {
            $store = $this->getCustomer()->getStore();
        }
        return $this->priceCurrency->format(
            $price,
            true,
            PriceCurrencyInterface::DEFAULT_PRECISION,
            $store
        );
    }

    public function showPriceIncTax()
    {
        return $this->_getLayoutParam('priceFormat') == 'includeTax';
    }

    public function showDiscount()
    {
        return $this->_getLayoutParam('discount') == 'yes';
    }

    public function showImage()
    {
        return $this->_getLayoutParam('image') == 'yes';
    }

    public function showSpecialPrice()
    {
        return $this->_getLayoutParam('showSpecialPrice') == 'yes';
    }

    protected function _getLayoutParam($key)
    {
        $func = 'get' . $key;
        return in_array($this->$func(), $this->_params[$key]['available']) ? $this->$func() : $this->_params[$key]['default'];
    }

    public function getDiscountPrice($price)
    {
        $discountPrice = $price;

        $sceduleId = $this->getHistory()->getScheduleId();
        $schedule = $this->objectManager->create(
            '\Amasty\Followup\Model\Schedule'
        );
        $schedule->load($sceduleId);

        if ($schedule->getDiscountAmount()) {
            switch ($schedule->getCouponType()) {
                case "by_percent":

                    $discountPrice -= $discountPrice * $schedule->getDiscountAmount() / 100;
                    break;
                case "by_fixed":
                    $discountPrice -= $schedule->getDiscountAmount();
                    break;
            }
        }

        return $discountPrice;
    }

    public function getProductUrl($item)
    {
        $this->_initUrlManager();

        if ($item->getRedirectUrl()) {
            return $item->getRedirectUrl();
        }

        $product = $item;

        $option = $item->getOptionByCode('product_type');
        if ($option) {
            $product = $option->getProduct();
        }

        return $this->_urlManager->get($product->getUrlModel()->getUrl($product));
    }

    protected function _initUrlManager()
    {
        if (!$this->_urlManager->getRule()) {
            $this->_urlManager->init($this->getHistory());
        }
    }

    public function getProductImageHelper()
    {
        return $this->_imageHelper;
    }

    public function initProductImageHelper($visibleItem, $imageId)
    {
        $product = $visibleItem;

        if ($this->getQuote()) {
            foreach ($this->getQuote()->getAllItems() as $item) {
                if ($item->getParentItemId() && $item->getParentItemId() == $visibleItem->getId()) {
                    $product = $item;
                    break;
                }
            }
        }

        $this->_imageHelper->init($product, $imageId);
    }

    public function getDescription($product)
    {
        $desc = '';

        if (!$this->hideDescription()) {
            $desc = $this->showShortDescription() ? $product->getShortDescription() : $product->getDescription();
        }

        return $desc;
    }

    public function hideDescription()
    {
        return $this->_getLayoutParam('descriptionFormat') == 'no';
    }

    public function showShortDescription()
    {
        return $this->_getLayoutParam('descriptionFormat') == 'short';
    }

    public function showPrice()
    {
        return $this->_getLayoutParam('price') == 'yes';
    }

    /**
     * @param $item
     * @return \Magento\Catalog\Api\Data\ProductInterface|null
     */
    public function loadProduct($item)
    {
        $product = null;

        if ($item instanceof \Magento\Catalog\Model\Product) {
            $product = $this->productRepository->getById($item->getId());
        } elseif ($item->getQuote()) {
            $product = $this->productRepository->getById($item->getProductId(), false, $item->getQuote()->getStoreId());
        } else {
            $product = $item->getProduct();
        }

        return $product;
    }

    public function getDate($date, $includeTime = false)
    {
        return $this->dateTime->formatDate($date, $includeTime);
    }

    public function displayFormatQty($qty = 0)
    {
        return ($qty) ? '&nbsp;x&nbsp;' . $qty : '';
    }
}
