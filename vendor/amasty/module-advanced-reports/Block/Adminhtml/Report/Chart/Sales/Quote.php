<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


declare(strict_types=1);

namespace Amasty\Reports\Block\Adminhtml\Report\Chart\Sales;

use Amasty\Reports\Block\Adminhtml\Report\Chart;

class Quote extends Chart
{
    public function getDefaultDisplayType(): string
    {
        return 'total';
    }
}
