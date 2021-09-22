<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPaypal
 */


declare(strict_types=1);

namespace Amasty\RecurringPaypal\Plugin\App\Action;

use Amasty\RecurringPaypal\Model\Subscription\Cache as SubscriptionCache;
use Amasty\RecurringPaypal\Model\Subscription\Confirmation\LinksPersistor;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;

class Action
{
    /**
     * @var LinksPersistor
     */
    private $linksPersistor;

    /**
     * @var SubscriptionCache
     */
    private $subscriptionCache;

    public function __construct(
        LinksPersistor $linksPersistor,
        SubscriptionCache $subscriptionCache
    ) {
        $this->linksPersistor = $linksPersistor;
        $this->subscriptionCache = $subscriptionCache;
    }

    public function beforeDispatch(\Magento\Framework\App\Action\Action $subject, RequestInterface $request)
    {
        if ($request->getParam('ba_token')) {
            if ($subscriptionId = $request->getParam('subscription_id')) {
                $this->subscriptionCache->clearSubscriptionData($subscriptionId);
            }
            // Prevent paypal misbehavior
            $this->redirect($subject, strtok((string)$request->getUri(), '?'));
        } elseif ($confirmationUrl = $this->linksPersistor->pop()) {
            $this->redirect($subject, $confirmationUrl);
        }
    }

    protected function redirect(\Magento\Framework\App\Action\Action $action, string $targetUrl)
    {
        /** @var \Magento\Framework\App\Response\Http $response */
        $response = $action->getResponse();
        $response->setRedirect($targetUrl);

        $action->getActionFlag()->set('', ActionInterface::FLAG_NO_DISPATCH, true);
    }
}
