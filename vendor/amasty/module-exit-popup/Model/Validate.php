<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ExitPopup
 */


namespace Amasty\ExitPopup\Model;

use Amasty\ExitPopup\Model\ConfigProvider;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Validator\EmailAddress as EmailValidator;
use Magento\Store\Model\StoreManagerInterface;

class Validate
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var AccountManagementInterface
     */
    private $customerAccountManagement;

    /**
     * @var EmailValidator
     */
    private $emailValidator;

    public function __construct(
        ConfigProvider $configProvider,
        CustomerSession $customerSession,
        StoreManagerInterface $storeManager,
        AccountManagementInterface $customerAccountManagement,
        EmailValidator $emailValidator
    ) {
        $this->configProvider = $configProvider;
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->emailValidator = $emailValidator;
    }

    /**
     * @return bool
     */
    public function validateGuestSubscription()
    {
        if (!$this->configProvider->allowGuestSubscribe()) {
            if (!$this->customerSession->isLoggedIn()) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $email
     *
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function validateEmailAvailable($email)
    {
        $websiteId = $this->storeManager->getStore()->getWebsiteId();

        if ($this->customerSession->getCustomerDataObject()->getEmail() !== $email
            && !$this->customerAccountManagement->isEmailAvailable($email, $websiteId)
        ) {
            return false;
        }

        return true;
    }

    /**
     * @param string $email
     *
     * @return bool
     */
    public function validateEmailFormat($email)
    {
        return $this->emailValidator->isValid($email);
    }
}
