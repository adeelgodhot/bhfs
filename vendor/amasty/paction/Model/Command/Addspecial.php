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

class Addspecial extends Addprice
{
    const TYPE = 'addspecial';

    /**
     * @var string
     */
    protected $sourceAttributeCode = 'price';

    /**
     * @var string
     */
    protected $attributeCodeToModify = 'special_price';

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
            'confirm_title' => __('Modify Special Price using Price')->render(),
            'confirm_message' => __('Are you sure you want to modify special price using price?')->render(),
            'type' => $this->type,
            'label' => __('Modify Special Price using Price')->render()
        ]);
    }
}
