<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Fpc
 */

declare(strict_types=1);

namespace Amasty\Fpc\Plugin\App\Http;

use Magento\Customer\Model\Group;
use Magento\Framework\App\Http\Context;

class ContextPlugin
{
    /**
     * After customer is logged $groupId will be integer and this causes problem with page being warmed
     *
     * @param Context $subject
     */
    public function beforeGetVaryString(Context $subject)
    {
        if ($groupId = $subject->getValue(\Magento\Customer\Model\Context::CONTEXT_GROUP)) {
            $subject->setValue(
                \Magento\Customer\Model\Context::CONTEXT_GROUP,
                (string)$groupId,
                Group::NOT_LOGGED_IN_ID
            );
        }
    }
}
