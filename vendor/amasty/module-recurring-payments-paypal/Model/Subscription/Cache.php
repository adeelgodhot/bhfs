<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPaypal
 */


declare(strict_types=1);

namespace Amasty\RecurringPaypal\Model\Subscription;

use Magento\Framework\App\CacheInterface;

class Cache
{
    const TYPE_IDENTIFIER = 'amasty_recurring';
    const CACHE_TAG = 'amasty_recurring';
    const SUBSCRIPTION_ENTITY_KEY = 'subscription';
    const BROKEN_RECORD = 'broken';
    const LIFETIME = 3600 * 24;

    /**
     * @var CacheInterface
     */
    private $cache;

    public function __construct(
        CacheInterface $cache
    ) {
        $this->cache = $cache;
    }

    public function saveSubscriptionData(array $data): bool
    {
        unset($data['subscriber']); // Clear personal data

        return $this->cache->save(
            json_encode($data),
            $this->getSubscriptionKey($data['id']),
            [self::CACHE_TAG],
            self::LIFETIME
        );
    }

    public function clearSubscriptionData(string $subscriptionId): bool
    {
        return $this->cache->remove($this->getSubscriptionKey($subscriptionId));
    }

    /**
     * @param string $subscriptionId
     * @return array|bool|string
     */
    public function getSubscriptionData(string $subscriptionId)
    {
        $data = $this->cache->load($this->getSubscriptionKey($subscriptionId));
        if ($data && $data != self::BROKEN_RECORD) {
            return json_decode($data, true);
        }

        return $data;
    }

    public function markAsBroken(string $subscriptionId)
    {
        return $this->cache->save(self::BROKEN_RECORD, $this->getSubscriptionKey($subscriptionId));
    }

    protected function getSubscriptionKey(string $subscriptionId)
    {
        return self::TYPE_IDENTIFIER . '_' . self::SUBSCRIPTION_ENTITY_KEY . '_' . $subscriptionId;
    }
}
