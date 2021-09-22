<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ThankYouPage
 */

namespace Amasty\ThankYouPage\Controller\Newsletter;

use Amasty\ThankYouPage\Api\ConfigNewsletterInterface;
use Amasty\ThankYouPage\Model\Config;
use Amasty\ThankYouPage\Model\Template\Filter;
use Magento\Customer\Api\AccountManagementInterface as CustomerAccountManagement;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Validator\EmailAddress as EmailValidator;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Store\Model\StoreManagerInterface;

class Subscribe extends Action
{
    /**
     * Customer session
     *
     * @var Session
     */
    private $customerSession;

    /**
     * Subscriber factory
     *
     * @var SubscriberFactory
     */
    private $subscriberFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CustomerUrl
     */
    private $customerUrl;

    /**
     * @var ConfigNewsletterInterface
     */
    private $blockConfig;

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var Config
     */
    private $configProvider;

    /**
     * @var CustomerAccountManagement
     */
    protected $customerAccountManagement;

    /**
     * @var EmailValidator
     */
    private $emailValidator;

    public function __construct(
        Context $context,
        SubscriberFactory $subscriberFactory,
        Session $customerSession,
        StoreManagerInterface $storeManager,
        CustomerUrl $customerUrl,
        ConfigNewsletterInterface $blockConfig,
        Filter $filter,
        Config $configProvider,
        CustomerAccountManagement $customerAccountManagement,
        EmailValidator $emailValidator
    ) {
        parent::__construct($context);
        $this->subscriberFactory = $subscriberFactory;
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->customerUrl = $customerUrl;
        $this->filter = $filter;
        $this->configProvider = $configProvider;
        $this->blockConfig = clone $blockConfig;
        $this->blockConfig->setGroupPrefix('block_newsletter');
        $this->customerAccountManagement = $customerAccountManagement;
        $this->emailValidator = $emailValidator;
    }

    /**
     * New subscription action
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $message = $this->filter->filter($this->blockConfig->getConfirmationMessage());

        if ($this->getRequest()->isPost() && $this->getRequest()->getPost('email')) {
            $email = (string)$this->getRequest()->getPost('email');

            try {
                $this->validateEmailFormat($email);
                $this->validateGuestSubscription();
                $this->validateEmailAvailable($email);

                $subscriber = $this->subscriberFactory->create();
                $status = $subscriber->subscribe($email);
                if ($status == \Magento\Newsletter\Model\Subscriber::STATUS_NOT_ACTIVE) {
                    $message = __('The confirmation request has been sent.');
                } elseif (!$subscriber->isStatusChanged()
                    && ($status == \Magento\Newsletter\Model\Subscriber::STATUS_SUBSCRIBED)
                ) {
                    $message = $this->blockConfig->getAlreadySubscribedText();
                }
            } catch (\Exception $e) {
                unset($e);
            }
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        // return success regardless of any result
        $resultJson->setData(['success' => true, 'message' => $message]);

        return $resultJson;
    }

    /**
     * Validates the format of the email address
     *
     * @param string $email
     * @throws LocalizedException
     * @return void
     */
    protected function validateEmailFormat($email)
    {
        if (!$this->emailValidator->isValid($email)) {
            throw new LocalizedException(__('Please enter a valid email address.'));
        }
    }

    /**
     * Validates that if the current user is a guest, that they can subscribe to a newsletter.
     *
     * @throws LocalizedException
     * @return void
     */
    protected function validateGuestSubscription()
    {
        if ($this->configProvider->getAllowGuestSubscribe() != 1 && !$this->customerSession->isLoggedIn()) {
            throw new LocalizedException(
                __(
                    'Sorry, but the administrator denied subscription for guests. Please <a href="%1">register</a>.',
                    $this->customerUrl->getRegisterUrl()
                )
            );
        }
    }

    /**
     * Validates that the email address isn't being used by a different account.
     *
     * @param string $email
     * @throws LocalizedException
     * @return void
     */
    protected function validateEmailAvailable($email)
    {
        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        if ($this->customerSession->isLoggedIn()
            && ($this->customerSession->getCustomerDataObject()->getEmail() !== $email
                && !$this->customerAccountManagement->isEmailAvailable($email, $websiteId))
        ) {
            throw new LocalizedException(
                __('This email address is already assigned to another user.')
            );
        }
    }
}
