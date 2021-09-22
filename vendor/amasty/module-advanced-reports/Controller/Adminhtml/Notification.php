<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


declare(strict_types=1);

namespace Amasty\Reports\Controller\Adminhtml;

use Magento\Backend\App\Action;

abstract class Notification extends Action
{
    const ADMIN_RESOURCE = 'Amasty_Reports::notification';
}
