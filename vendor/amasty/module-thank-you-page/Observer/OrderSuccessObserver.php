<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ThankYouPage
 */

namespace Amasty\ThankYouPage\Observer;

use Amasty\ThankYouPage\Block\Onepage\Success\Facade;
use Magento\Framework\Module\Manager;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\Result\PageFactory;

class OrderSuccessObserver implements ObserverInterface
{

    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Manager
     */
    private $moduleManager;

    /**
     * @param PageFactory $pageFactory
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        PageFactory $pageFactory,
        ScopeConfigInterface $scopeConfig,
        Manager $moduleManager
    ) {
        $this->pageFactory = $pageFactory;
        $this->scopeConfig = $scopeConfig;
        $this->moduleManager = $moduleManager;
    }

    /**
     * If Varnish caching is enabled it collects array of tags
     * of incoming object and asks to clean cache.
     *
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer)
    {
        if (!$this->scopeConfig->isSetFlag('amasty_thank_you_page/general/enable')) {
            return;
        }

        $page = $this->pageFactory->create();
        $layout = $page->getLayout();

        // unset default blocks from success page
        $blocksToUnset = [
            'checkout.registration',
            'page.main.title',
        ];

        if (!$this->scopeConfig->isSetFlag('amasty_thank_you_page/block_order_review/display')) {
            $blocksToUnset[] = 'amasty.checkout.success';
            $blocksToUnset[] = 'checkout.success';
            $blocksToUnset[] = 'checkout_success';
            if ($this->moduleManager->isEnabled('Amasty_JetTheme')) {
                $blocksToUnset[] = 'amtheme.checkout.laststep';
                $blocksToUnset[] = 'checkout.success.print.button';
            }
        } else {
            $layout->unsetChild('content', 'amasty.checkout.success');
            $layout->unsetChild('content', 'checkout.success');
            $layout->unsetChild('content', 'checkout_success');
        }

        array_walk($blocksToUnset, [$layout, 'unsetElement']);

        // render the facade
        $layout->createBlock(Facade::class, 'amasty.thankyoupage.facade');
        $googleAnalyticsUniversal = $layout->getBlock('google_analyticsuniversal');
        $orderIds = $observer->getEvent()->getOrderIds();
        if ($googleAnalyticsUniversal && !empty($orderIds) && is_array($orderIds)) {
            $googleAnalyticsUniversal->setOrderIds($orderIds);
        }

        $layout->setChild('content', 'amasty.thankyoupage.facade', 'amasty.thankyoupage.facade');
    }
}
