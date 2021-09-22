<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Controller\Adminhtml\Label\Edit;

use Amasty\Label\Controller\Adminhtml\Label\Edit;
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context as AdminActionContext;
use Magento\Catalog\Model\ImageUploader;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;

class UploadImage extends Action implements HttpPostActionInterface
{
    const ADMIN_RESOURCE = Edit::ADMIN_RESOURCE;

    /**
     * @var ImageUploader
     */
    protected $imageUploader;

    public function __construct(
        AdminActionContext $context,
        ImageUploader $imageUploader
    ) {
        parent::__construct($context);

        $this->imageUploader = $imageUploader;
    }

    public function execute()
    {
        $imageId = $this->_request->getParam('param_name', 'image');

        try {
            $result = $this->imageUploader->saveFileToTmpDir($imageId);
        } catch (Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }

        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }
}
