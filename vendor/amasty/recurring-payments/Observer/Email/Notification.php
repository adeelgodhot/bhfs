<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Observer\Email;

use Magento\Framework\App\Area;
use Magento\Framework\Event\{Observer, ObserverInterface};
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\Store;

/**
 * Class Notification
 */
class Notification implements ObserverInterface
{
    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    public function __construct(TransportBuilder $transportBuilder)
    {
        $this->transportBuilder = $transportBuilder;
    }

    /**
     * @param Observer $observer
     *
     * @throws \Exception
     */
    public function execute(Observer $observer)
    {
        $data = $observer->getData('email_data');

        if (!$data) {
            throw new \Exception('Email data not specified');
        }

        /** @var TransportBuilder $transportBuilder */
        $transportBuilder = clone $this->transportBuilder;

        $transportBuilder->setTemplateIdentifier($data['template'] ?? null)
            ->setTemplateVars($data['template_variables'] ?? [])
            ->setTemplateOptions(
                [
                    Area::PARAM_AREA=> Area::AREA_FRONTEND,
                    Store::ENTITY => $data['store_id'] ?? null
                ]
            )
            ->setFrom($data['email_sender'] ?? null)
            ->addTo($data['email_recipient'] ?? null);

        /** @var \Magento\Framework\Mail\TransportInterface $transport */
        $transport = $transportBuilder->getTransport();

        $transport->sendMessage();
    }
}
