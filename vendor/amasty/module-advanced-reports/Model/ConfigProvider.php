<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


declare(strict_types=1);

namespace Amasty\Reports\Model;

class ConfigProvider extends \Amasty\Base\Model\ConfigProviderAbstract
{
    const MODULE_SECTION = 'amasty_reports/';
    const XPATH_NOTIFICATION_SENDER = 'general/sender_email_identity';
    const XPATH_NOTIFICATION_TEMPLATE = 'general/email_template';

    protected $pathPrefix = self::MODULE_SECTION;

    public function getNotificationSender(): ?string
    {
        return $this->getValue(self::XPATH_NOTIFICATION_SENDER);
    }

    public function getNotificationTemplate(): ?string
    {
        return $this->getValue(self::XPATH_NOTIFICATION_TEMPLATE);
    }
}
