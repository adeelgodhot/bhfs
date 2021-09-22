<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Paction
 */

declare(strict_types=1);

namespace Amasty\Paction\Model\Command;

use Amasty\Paction\Model\Command;
use Magento\Backend\Model\UrlInterface;
use Magento\Catalog\Helper\Product\Edit\Action\Attribute;
use Magento\Framework\App\Response\Http;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\LocalizedException;

class Updateadvancedprices extends Command
{
    const TYPE = 'updateadvancedprices';

    /**
     * @var Http
     */
    private $response;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var Attribute
     */
    private $attributeHelper;

    public function __construct(
        Http $response,
        UrlInterface $url,
        Attribute $attributeHelper
    ) {
        $this->response = $response;
        $this->url = $url;
        $this->attributeHelper = $attributeHelper;

        $this->type = self::TYPE;
        $this->info = [
            'confirm_title' => __('Update Advanced Prices')->render(),
            'confirm_message' => __('Are you sure you want to update prices?')->render(),
            'type' => $this->type,
            'label' => __('Update Advanced Prices')->render(),
            'fieldLabel' => ''
        ];
    }

    public function execute(array $ids, int $storeId, string $val): ResponseInterface
    {
        if (!$ids) {
            throw new LocalizedException(__('Please select product(s)'));
        }
        $url = $this->url->getUrl(
            'catalog/product_action_attribute/edit',
            ['_current' => true, 'active_tab' => 'tier_prices']
        );
        $this->attributeHelper->setProductIds($ids);

        return $this->response->setRedirect($url);
    }
}
