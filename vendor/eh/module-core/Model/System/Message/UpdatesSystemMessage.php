<?php
/**
 * @author ExtensionHut Team
 * @copyright Copyright (c) 2020 ExtensionHut (https://www.extensionhut.com/)
 * @package EH_Core
 */

namespace EH\Core\Model\System\Message;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Notification\MessageInterface;
use Magento\Framework\UrlInterface;
use EH\Core\Model\Processor;

/**
 * Class UpdatesSystemMessage
 * @package EH\Core\Model\System\Message
 */
class UpdatesSystemMessage implements MessageInterface
{
	/**
	 * @var Processor
	 */
	protected $processor;

	/**
	 * @var $updateAvailable
	 */
	protected $updateAvailable;

	/**
	 * @var $extensionDetails
	 */
	protected $extensionDetails = [];

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * UpdatesSystemMessage constructor.
     * @param Processor $processor
     * @param UrlInterface $url
     * @param ScopeConfigInterface $scopeConfig
     */
	public function __construct(
        Processor $processor,
        UrlInterface $url,
        ScopeConfigInterface $scopeConfig
	) {
		$this->processor = $processor;
        $this->scopeConfig = $scopeConfig;
        $this->urlBuilder = $url;
	}

	/**
	* Retrieve unique system message identity
	*
	* @return string
	*/
	public function getIdentity()
	{
	   return hash('sha256', 'EH_UPDATES_NOTIFICATION');
	}

	/**
	* Retrieve system message severity
	* Possible default system message types:
	* - MessageInterface::SEVERITY_CRITICAL
	* - MessageInterface::SEVERITY_MAJOR
	* - MessageInterface::SEVERITY_MINOR
	* - MessageInterface::SEVERITY_NOTICE
	*
	* @return int
	*/
	public function getSeverity()
	{
	   return self::SEVERITY_MAJOR;
	}

	/**
	* Check whether the system message should be shown
	*
	* @return bool
	*/
	public function isDisplayed()
	{
		return false;
	}

	/**
	* Retrieve system message text
	*
	* @return boolean
	*/
	public function getText()
	{
		return false;
	}

	/**
	* Check if extension update needed
	*
	* @return mixed
	*/
    protected function _checkUpdate($extensionName, $extensionLabel)
    {
        $extensionDetails = $this->processor->getExtensionVersion($extensionName);
        if (isset($extensionDetails['status']) && isset($extensionDetails['update_needed']) &&
            $extensionDetails['status'] && $extensionDetails['update_needed']) {
            if (isset($extensionDetails['label'])) {
                $extensionLabel = $extensionDetails["label"];
            }
            $availableVersion = $extensionDetails['available_version'];
            if ($this->_checkIfRead($extensionName, $availableVersion)) {
                return false;
            }
            $routePath = "extensionhut/system_message/markRead/extension/$extensionName/version/$availableVersion";
            $msg = $extensionLabel . ' ' . $extensionDetails['status_message'] .
                ' ' .
                $extensionDetails['notification_msg'] .
                ' ' .
                "<b>
                    [<a href='{$this->urlBuilder->getUrl($routePath)}'>Mark as Read</a>]
                </b>";
            return $msg;
        }
        return false;
    }

    /**
     * @param $extensionName
     * @param $availableVersion
     * @return bool
     */
    protected function _checkIfRead($extensionName, $availableVersion)
    {
        $value = $this->scopeConfig->getValue(Processor::XML_BASE_CONFIG_PATH . $extensionName);
        if ($value && version_compare($value, $availableVersion) >= 0) {
            return true;
        }
        return false;
    }

	/**
	* Get current extension details
	*
	* @return array
	*/
	protected function _getExtensionDetails($class)
    {
        if (empty($this->extensionDetails)) {
            $class = get_class($class);
            if ($class) {
                $class = explode('\\', $class);
                if (isset($class[0]) && isset($class[1])) {
                    $this->extensionDetails['name'] = $class[0] . '_' . $class[1];
                    $this->extensionDetails['label'] = $class[1];
                }
            }
        }

        return $this->extensionDetails;
    }
}
