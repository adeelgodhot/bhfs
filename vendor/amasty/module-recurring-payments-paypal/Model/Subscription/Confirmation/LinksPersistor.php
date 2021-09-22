<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPaypal
 */


declare(strict_types=1);

namespace Amasty\RecurringPaypal\Model\Subscription\Confirmation;

use Magento\Framework\Session\SessionManagerInterface;

class LinksPersistor
{
    const REDIRECT_URL_KEY = 'amasty_recurring_redirect_url';

    /**
     * @var SessionManagerInterface
     */
    private $sessionManager;

    public function __construct(
        SessionManagerInterface $sessionManager
    ) {
        $this->sessionManager = $sessionManager;
    }

    public function push(string $link)
    {
        $data = $this->sessionManager->getData(self::REDIRECT_URL_KEY) ?: [];
        $data[] = $link;
        $this->sessionManager->setData(self::REDIRECT_URL_KEY, $data);
    }

    public function pop(): string
    {
        $result = '';
        $data = $this->sessionManager->getData(self::REDIRECT_URL_KEY);
        if (is_array($data) && !empty($data)) {
            $result = array_shift($data);
            if (!empty($data)) {
                $this->sessionManager->setData(self::REDIRECT_URL_KEY, $data);
            } else {
                $this->sessionManager->unsetData(self::REDIRECT_URL_KEY);
            }
        }

        return $result;
    }
}
