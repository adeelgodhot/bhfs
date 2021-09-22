<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ExitPopup
 */


namespace Amasty\ExitPopup\Model;

use Amasty\ExitPopup\Api\EmailManagementInterface;
use Amasty\ExitPopup\Model\ConfigProvider;
use Amasty\ExitPopup\Model\Promo;
use Amasty\ExitPopup\Model\Subscription;
use Amasty\ExitPopup\Model\DownloadableProduct;
use Psr\Log\LoggerInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Area;
use Amasty\ExitPopup\Model\Config\Source\PromoSource;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 * @codingStandardsIgnoreStart
 */
class EmailManagement implements EmailManagementInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Promo
     */
    private $promo;

    /**
     * @var Subscription
     */
    private $subscription;

    /**
     * @var DownloadableProduct
     */
    private $downloadable;

    public function __construct(
        LoggerInterface $logger,
        TransportBuilder $transportBuilder,
        ConfigProvider $configProvider,
        StoreManagerInterface $storeManager,
        Promo $promo,
        Subscription $subscription,
        DownloadableProduct $downloadable
    ) {
        $this->logger = $logger;
        $this->transportBuilder = $transportBuilder;
        $this->configProvider = $configProvider;
        $this->storeManager = $storeManager;
        $this->promo = $promo;
        $this->subscription = $subscription;
        $this->downloadable = $downloadable;
    }

    /**
     * @inheritdoc
     */
    public function sendEmail($email)
    {
        $this->subscription->subscribeCustomer($email);

        $storeId = $this->storeManager->getStore()->getId();
        $content = $this->getTemplateVariable();

        $templateVars = ['freebie' => $content];

        $transport = $this->transportBuilder->setTemplateIdentifier(
            $this->configProvider->getEmailTemplate()
        )->setTemplateOptions(
            ['area' => Area::AREA_FRONTEND, 'store' => $storeId]
        )->setFrom(
            $this->configProvider->getEmailSender()
        )->setTemplateVars(
            $templateVars
        )->addTo(
            $email
        )->getTransport();

        try {
             $transport->sendMessage();
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }

        return true;
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    private function getTemplateVariable()
    {
        $content = '';

        if ($this->configProvider->getPromoType() == PromoSource::COUPON_CODE_VALUE) {
            $content = $this->promo->getPromoCodeByRuleId($this->configProvider->getRuleId());
        } elseif ($this->configProvider->getPromoType() == PromoSource::PRODUCT_VALUE) {
            $content = $this->downloadable->getDownloadLink($this->configProvider->getProductId());
        }

        return $content;
    }
}
