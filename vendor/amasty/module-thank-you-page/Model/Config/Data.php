<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ThankYouPage
 */

namespace Amasty\ThankYouPage\Model\Config;

use Magento\Framework\Config\DataInterface;
use Magento\Framework\Config\ReaderInterface;
use Magento\Framework\Config\CacheInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Amasty\Base\Model\Serializer as AmSerializer;
use Magento\Framework\App\ObjectManager;

/**
 * Backwards compatibility with Magento 2.1
 * Represents loaded and cached configuration data, should be used to gain access to different types
 *
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
class Data implements DataInterface
{

    /**
     * Cache tags
     *
     * @var array
     */
    protected $cacheTags = [];

    /**
     * Config data
     *
     * @var array
     */
    protected $data = [];

    /**
     * @var ReaderInterface
     */
    private $reader;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var string
     */
    private $cacheId;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * Constructor
     *
     * @param ReaderInterface $reader
     * @param CacheInterface $cache
     * @param string $cacheId
     */
    public function __construct(
        ReaderInterface $reader,
        CacheInterface $cache,
        $cacheId
    ) {
        $this->reader = $reader;
        $this->cache = $cache;
        $this->cacheId = $cacheId;

        // Backwards compatibility with Magento 2.1
        $this->serializer = class_exists(SerializerInterface::class)
            ? ObjectManager::getInstance()->get(SerializerInterface::class)
            : ObjectManager::getInstance()->get(AmSerializer::class);

        $this->initData();
    }

    /**
     * Initialise data for configuration
     *
     * @return void
     */
    protected function initData()
    {
        $data = $this->cache->load($this->cacheId);
        if (false === $data) {
            $data = $this->reader->read();
            $this->cache->save($this->serializer->serialize($data), $this->cacheId, $this->cacheTags);
        } else {
            $data = $this->serializer->unserialize($data);
        }

        $this->merge($data);
    }

    /**
     * Merge config data to the object
     *
     * @param array $config
     *
     * @return void
     */
    public function merge(array $config)
    {
        $this->data = array_replace_recursive($this->data, $config);
    }

    /**
     * Get config value by key
     *
     * @param string $path
     * @param mixed $default
     *
     * @return array|mixed|null
     */
    public function get($path = null, $default = null)
    {
        if ($path === null) {
            return $this->data;
        }
        $keys = explode('/', $path);
        $data = $this->data;
        foreach ($keys as $key) {
            if (is_array($data) && array_key_exists($key, $data)) {
                $data = $data[$key];
            } else {
                return $default;
            }
        }
        return $data;
    }

    /**
     * Clear cache data
     *
     * @return void
     */
    public function reset()
    {
        $this->cache->remove($this->cacheId);
    }
}
