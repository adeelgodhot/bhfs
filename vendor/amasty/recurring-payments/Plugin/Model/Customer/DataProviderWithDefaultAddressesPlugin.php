<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Plugin\Model\Customer;

use Amasty\RecurringPayments\Model\SubscriptionManagement;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Psr\Log\LoggerInterface;

/**
 * Class DataProviderWithDefaultAddressesPlugin
 */
class DataProviderWithDefaultAddressesPlugin
{
    /**
     * @var SubscriptionManagement
     */
    private $subscriptionManagement;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var Repository
     */
    private $assetRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        SubscriptionManagement $subscriptionManagement,
        UrlInterface $urlBuilder,
        RequestInterface $request,
        Repository $assetRepository,
        LoggerInterface $logger
    ) {
        $this->subscriptionManagement = $subscriptionManagement;
        $this->urlBuilder = $urlBuilder;
        $this->request = $request;
        $this->assetRepository = $assetRepository;
        $this->logger = $logger;
    }

    /**
     * @param AbstractDataProvider $subject
     * @param array $result
     *
     * @return array
     */
    public function afterGetData(AbstractDataProvider $subject, array $result): array
    {
        if (!empty($result)) {
            foreach ($result as $customerId => $customer) {
                $result[$customerId] += [
                    'subscriptions' => $this->subscriptionManagement->getSubscriptions((int)$customerId),
                    'cancelUrl' => $this->urlBuilder->getUrl(
                        'amasty_recurring/customer/cancelSubscription',
                        [
                            'customer_id' => $this->request->getParam('id')
                        ]
                    ),
                    'loaderUrl' => $this->getViewFileUrl('images/loader-1.gif')
                ];
            }
        }

        return $result;
    }

    /**
     * Retrieve url of a view file
     *
     * @param string $fileId
     * @param array $params
     *
     * @return string
     */
    private function getViewFileUrl($fileId, array $params = []): string
    {
        try {
            $params = array_merge(['_secure' => $this->request->isSecure()], $params);
            return $this->assetRepository->getUrlWithParams($fileId, $params);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->logger->critical($e);
            return $this->getNotFoundUrl();
        }
    }

    /**
     * Get 404 file not found url
     *
     * @param string $route
     * @param array $params
     *
     * @return string
     */
    private function getNotFoundUrl($route = '', $params = ['_direct' => 'core/index/notFound']): string
    {
        return $this->urlBuilder->getUrl($route, $params);
    }
}
