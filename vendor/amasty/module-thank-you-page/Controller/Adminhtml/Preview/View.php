<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ThankYouPage
 */


namespace Amasty\ThankYouPage\Controller\Adminhtml\Preview;

use Amasty\ThankYouPage\Plugin\Session\Model\PreviewSuccess;
use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Config\CacheInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Url;

class View extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Amasty_ThankYouPage::preview';

    const SUCCESS_PAGE_URL = 'checkout/onepage/success';

    /**
     * @var Url
     */
    private $urlFramework;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var CacheInterface
     */
    private $cache;

    public function __construct(
        Action\Context $context,
        Url $urlFramework,
        EncryptorInterface $encryptor,
        CacheInterface $cache
    ) {
        parent::__construct($context);
        $this->urlFramework = $urlFramework;
        $this->encryptor = $encryptor;
        $this->cache = $cache;
    }

    /**
     * @return ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        $url = $this->urlFramework->getUrl(
            self::SUCCESS_PAGE_URL,
            array_merge($this->getRequest()->getParams(), [PreviewSuccess::CHECK_PARAM => $this->getPreviewKey()])
        );

        $resultRedirect->setUrl($url);
        $resultRedirect->setHttpResponseCode(301);

        return $resultRedirect;
    }

    /**
     * @return string
     */
    private function getPreviewKey()
    {
        $key = $this->encryptor->encrypt(PreviewSuccess::PREVIEW_KEY);
        $this->saveKey($key);

        return $key;
    }

    /**
     * @param string $key
     */
    private function saveKey($key)
    {
        $this->cache->save($key, PreviewSuccess::PREVIEW_KEY, [PreviewSuccess::PREVIEW_KEY], 10);
    }
}
