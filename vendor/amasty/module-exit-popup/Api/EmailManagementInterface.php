<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ExitPopup
 */


namespace Amasty\ExitPopup\Api;

/**
 * @api
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 * @codingStandardsIgnoreStart
 */
interface EmailManagementInterface
{
    /**
     * @param string $email
     *
     * @return boolean
     */
    public function sendEmail($email);
}
