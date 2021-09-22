<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ExitPopup
 */


namespace Amasty\ExitPopup\Controller\ProductLink;

use Magento\Downloadable\Api\LinkRepositoryInterface;
use Magento\Downloadable\Helper\Download as DownloadHelper;
use Magento\Framework\App\Action\Context;
use Magento\Downloadable\Helper\File;
use Magento\Framework\Encryption\EncryptorInterface;

class Download extends \Magento\Downloadable\Controller\Download
{
    /**
     * @var LinkRepositoryInterface
     */
    private $linkRepository;

    /**
     * @var File
     */
    private $file;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    public function __construct(
        Context $context,
        LinkRepositoryInterface $linkRepository,
        File $file,
        EncryptorInterface $encryptor
    ) {
        $this->linkRepository = $linkRepository;
        $this->file = $file;
        $this->encryptor = $encryptor;
        parent::__construct($context);
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $sku = $this->encryptor->decrypt(urldecode($this->getRequest()->getParam('sku')));

        try {
            $linkItemsList = $this->linkRepository->getList($sku);
        } catch (\Exception $exception) {
            return $this->_redirect($this->_redirect->getRefererUrl());
        }

        if (count($linkItemsList)) {
            $linkItem = $linkItemsList[0];

            $resource = $this->file->getFilePath(
                $linkItem->getBasePath(),
                $linkItem->getLinkFile()
            );

            $resourceType = DownloadHelper::LINK_TYPE_FILE;

            $this->_processDownload($resource, $resourceType);
        } else {
            return $this->_redirect($this->_redirect->getRefererUrl());
        }
    }
}
