<?php
/**
 * @author ExtensionHut Team
 * @copyright Copyright (c) 2020 ExtensionHut (https://www.extensionhut.com/)
 * @package EH_Core
 */

namespace EH\Core\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class ConfigReader
 * @package EH\Core\Model
 */
class ConfigReader
{
    protected $_xmlBaseConfig = 'eh_core';

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var int
     */
    protected $_storeId;

    /**
     * @var DataObject
     */
    protected $_config;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var array $configValue
     */
    protected $configValue = [];

    /**
     * ConfigReader constructor.
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->_storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get store config
     *
     * @param string $scope
     * @param null $storeId
     * @return DataObject
     * @throws NoSuchEntityException
     */
    public function getConfig(
        $scope = ScopeInterface::SCOPE_STORE,
        $storeId = null
    )
    {
        $configBasePath = $this->_xmlBaseConfig;
        if (!$this->_config) {
            if (!$storeId) {
                $storeId = $this->getStoreId();
            }

            $configs = $this->scopeConfig->getValue(
                $configBasePath,
                $scope,
                $storeId
            );
            $settings = [];
            if ($configs) {
                foreach ($configs as $node => $config) {
                    $settings[$node] = new DataObject($config);
                }
            }

            $this->_config = new DataObject($settings);
        }
        return $this->_config;
    }

    /**
     * @param string $scope
     * @param null $storeId
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getGeneralConfig(
        $scope = ScopeInterface::SCOPE_STORE,
        $storeId = null
    )
    {
        return $this->getConfig($scope, $storeId)->getGeneral();
    }

    /**
     * @param $configPath
     * @param string $scope
     * @param null $storeId
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getConfigValue(
        $configPath,
        $scope = ScopeInterface::SCOPE_STORE,
        $storeId = null
    )
    {
        if (!isset($this->configValue[$configPath])) {
            if (!$storeId) {
                $storeId = $this->getStoreId();
            }

            $this->configValue[$configPath] = $this->scopeConfig->getValue($configPath, $scope, $storeId);
        }
        return $this->configValue[$configPath];
    }

    /**
     * Get current store id
     *
     * @return int
     * @throws NoSuchEntityException
     */
    public function getStoreId()
    {
        if ($this->_storeId === null) {
            $this->_storeId = (int)($this->_storeManager->getStore()->getId());
        }

        return $this->_storeId;
    }
}
