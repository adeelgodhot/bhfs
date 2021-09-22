<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ThankYouPage
 */

namespace Amasty\ThankYouPage\Api;

interface OrderCustomerManagementInterface
{

    /**
     * Create customer account for order
     *
     * @param int $orderId
     * @param null|string $email
     * @param null|string $password
     * @param null|string $dateOfBirth
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    public function create($orderId, $email = null, $password = null, $dateOfBirth = null);
}
