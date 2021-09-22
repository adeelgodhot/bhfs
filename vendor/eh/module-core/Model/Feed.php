<?php
/**
 * @author ExtensionHut Team
 * @copyright Copyright (c) 2020 ExtensionHut (https://www.extensionhut.com/)
 * @package EH_Core
 */

namespace EH\Core\Model;

use Magento\AdminNotification\Model\Feed as AdminNotificationFeed;

/**
 * Class Feed
 * @package EH\Core\Model
 */
class Feed extends AdminNotificationFeed
{
    const XML_FREQUENCY_PATH = "system/adminnotification/frequency";
    const EH_CACHE_KEY = "extensionhut_global_notifications_lastcheck";

    /**
     * Feed url
     *
     * @var string
     */
    protected $_feedUrl = "\x68\x74\x74\x70\x73\x3a\x2f\x2f\x77\x77\x77\x2e\x65\x78\x74\x65\x6e\x73\x69\x6f\x6e\x68\x75\x74\x2e\x63\x6f\x6d\x2f\x6d\x61\x67\x65\x6e\x74\x6f\x5f\x6e\x6f\x74\x69\x66\x69\x63\x61\x74\x69\x6f\x6e\x73\x2f\x66\x65\x65\x64\x5f\x67\x65\x6e\x65\x72\x61\x6c\x2e\x72\x73\x73";

    /**
     * Retrieve feed url
     *
     * @return string
     */
    public function getFeedUrl()
    {
        return $this->_feedUrl;
    }

    /**
     * Retrieve Last update time
     *
     * @return int
     */
    public function getLastUpdate()
    {
        return $this->_cacheManager->load(self::EH_CACHE_KEY);
    }

    /**
     * Set last update time (now)
     *
     * @return $this
     */
    public function setLastUpdate()
    {
        $this->_cacheManager->save(time(), self::EH_CACHE_KEY);
        return $this;
    }
}
