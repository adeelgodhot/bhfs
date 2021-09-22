<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Paction
 */

declare(strict_types=1);

namespace Amasty\Paction\Model\Command;

use Amasty\Paction\Model\ConfigProvider;
use Amasty\Paction\Model\EntityResolver;
use Magento\Eav\Model\Config;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;

class Modifycost extends Modifyprice
{
    const TYPE = 'modifycost';

    /**
     * @var string
     */
    protected $sourceAttributeCode = 'cost';

    public function __construct(
        Config $eavConfig,
        StoreManagerInterface $storeManager,
        ResourceConnection $resource,
        EntityResolver $entityResolver,
        ConfigProvider $configProvider
    ) {
        parent::__construct($eavConfig, $storeManager, $resource, $entityResolver, $configProvider);

        $this->type = self::TYPE;
        $this->info = array_merge($this->info, [
            'confirm_title' => __('Update Cost')->render(),
            'confirm_message' => __('Are you sure you want to update cost?')->render(),
            'type' => $this->type,
            'label' => __('Update Cost')->render()
        ]);
    }
}
