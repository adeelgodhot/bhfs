<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Paction
 */

declare(strict_types=1);

namespace Amasty\Paction\Model\Command;

use Amasty\Paction\Model\LinkActionsManagement;
use Magento\Framework\App\ResourceConnection;

class Uncrosssell extends Unrelate
{
    const TYPE = 'uncrosssell';

    public function __construct(ResourceConnection $resource, LinkActionsManagement $linkActionsManagement)
    {
        parent::__construct($resource, $linkActionsManagement);

        $this->type = self::TYPE;
        $this->info = [
            'confirm_title' => __('Remove Cross-Sells')->render(),
            'confirm_message' => __('Are you sure you want to remove cross-Sells?')->render(),
            'type' => $this->type,
            'label' => __('Remove Cross-Sells')->render(),
            'fieldLabel' => __('Select Algorithm')->render()
        ];
    }
}
