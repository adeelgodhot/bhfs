<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Plugin\Controller\Cart;

use Amasty\RecurringPayments\Api\Data\ProductRecurringAttributesInterface;
use Amasty\RecurringPayments\Model\Product;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product as MagentoProduct;
use Magento\Checkout\Controller\Cart\Add;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Escaper;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Psr\Log\LoggerInterface;

class AddPlugin
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var Product
     */
    private $product;

    /**
     * @var Configurable
     */
    private $configurable;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        Product $product,
        Configurable $configurable,
        ManagerInterface $messageManager,
        Escaper $escaper,
        RedirectFactory $redirectFactory,
        LoggerInterface $logger,
        SerializerInterface $serializer
    ) {
        $this->productRepository = $productRepository;
        $this->product = $product;
        $this->configurable = $configurable;
        $this->messageManager = $messageManager;
        $this->escaper = $escaper;
        $this->redirectFactory = $redirectFactory;
        $this->logger = $logger;
        $this->serializer = $serializer;
    }

    /**
     * @param Add $subject
     * @param \Closure $proceed
     *
     * @return Redirect
     */
    public function aroundExecute(Add $subject, \Closure $proceed)
    {
        $request = $subject->getRequest();

        if ($request->getParam('subscribe') == 'not_subscribe') {
            return $proceed();
        }

        try {
            if ($productId = $request->getParam('product')) {
                /** @var MagentoProduct $product */
                $product = $this->productRepository->getById($productId);

                if (!$this->isRequestValid($request, $product)) {
                    return $this->redirectOnError(
                        $this->configurable->getSpecifyOptionMessage()->render(),
                        $product->getProductUrl(),
                        $subject
                    );
                }
            }
        } catch (\Exception $exception) {
            $this->logger->critical($exception->getMessage());

            return $this->redirectOnError(
                $exception->getMessage()
            );
        }

        return $proceed();
    }

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param MagentoProduct $product
     * @return bool
     */
    private function isRequestValid(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Catalog\Model\Product $product
    ): bool {
        $isSubscribe = $request->getParam('subscribe') == 'subscribe';

        if ($this->product->isSubscriptionOnly($product) && !$isSubscribe) {
            return false;
        }

        if (!$isSubscribe) {
            return true;
        }

        $subscriptionPlanId = $request->getParam(ProductRecurringAttributesInterface::SUBSCRIPTION_PLAN_ID);
        if (!$subscriptionPlanId) {
            return false;
        }

        $plans = $this->product->getActiveSubscriptionPlans($product);
        foreach ($plans as $plan) {
            if ($plan->getPlanId() == $subscriptionPlanId) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $error
     * @param string|null $backUrl
     * @param Add|null $subject
     *
     * @return mixed
     */
    private function redirectOnError(string $error, string $backUrl = null, Add $subject = null)
    {
        $this->messageManager->addNoticeMessage($this->escaper->escapeHtml($error));

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->redirectFactory->create();

        if ($backUrl && $subject) {
            if ($subject->getRequest()->isAjax()) {
                return $subject->getResponse()->representJson(
                    $this->serializer->serialize(['backUrl' => $backUrl])
                );
            } else {
                $resultRedirect->setUrl($backUrl);
            }
        }

        return $resultRedirect;
    }
}
