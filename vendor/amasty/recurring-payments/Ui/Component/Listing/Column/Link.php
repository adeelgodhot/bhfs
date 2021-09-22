<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Block\Adminhtml\Order\View\Info;
use Magento\Sales\Model\OrderFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Link extends Column
{
    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var Info
     */
    private $info;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        OrderFactory $orderFactory,
        Info $info,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->orderFactory = $orderFactory;
        $this->info = $info;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item['entity_id'])) {
                    /** @var \Magento\Sales\Model\Order $order */
                    $order = $this->orderFactory->create();
                    $order->loadByIncrementId((int)$item['order_id']);

                    $item[$this->getData('name')] = $this->getLinkHtml($order);
                }
            }
        }

        return $dataSource;
    }

    /**
     * @param OrderInterface $order
     *
     * @return string
     */
    private function getLinkHtml(OrderInterface $order)
    {
        $link = $this->info->getViewUrl($order->getEntityId());

        return '<a target="_blank" href="' . $link . '">' . $order->getIncrementId() . '</a>';
    }
}
