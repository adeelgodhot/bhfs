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

class Unupsell extends Unrelate
{
    const TYPE = 'unupsell';

    public function __construct(ResourceConnection $resource, LinkActionsManagement $linkActionsManagement)
    {
        parent::__construct($resource, $linkActionsManagement);

        $this->type = self::TYPE;
        $this->info = [
            'confirm_title' => __('Remove Up-sells')->render(),
            'confirm_message' => __('Are you sure you want to remove up-sells?')->render(),
            'type' => $this->type,
            'label' => __('Remove Up-sells')->render(),
            'fieldLabel' => __('Select Algorithm')->render()
        ];
    }
}
