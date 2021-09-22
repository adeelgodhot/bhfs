<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringStripe
 */


declare(strict_types=1);

namespace Amasty\RecurringStripe\Model\Subscription;

use Magento\Framework\App\CacheInterface;

class Cache
{
    const TYPE_IDENTIFIER = 'amasty_recurring';
    const CACHE_TAG = 'amasty_recurring';
    const LIFETIME = 3600 * 24;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var InvoiceInfoFactory
     */
    private $infoFactory;

    public function __construct(
        CacheInterface $cache,
        InvoiceInfoFactory $infoFactory
    ) {
        $this->cache = $cache;
        $this->infoFactory = $infoFactory;
    }

    public function saveInvoiceInfo(string $entityId, InvoiceInfo $info): bool
    {
        return $this->cache->save(
            json_encode($info->getData()),
            $this->getEntityKey($entityId),
            [self::CACHE_TAG],
            self::LIFETIME
        );
    }

    /**
     * @param string $entityId
     * @return InvoiceInfo|bool
     */
    public function getInvoiceInfo(string $entityId)
    {
        $data = $this->cache->load($this->getEntityKey($entityId));

        if ($data && ($data = json_decode($data, true))) {
            /** @var InvoiceInfo $info */
            $info = $this->infoFactory->create();
            $info->setData($data);

            return $info;
        }

        return false;
    }

    protected function getEntityKey(string $entityId)
    {
        return self::TYPE_IDENTIFIER . '_' . $entityId;
    }
}
