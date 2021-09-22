<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImageOptimizer
 */

declare(strict_types=1);

namespace Amasty\ImageOptimizer\Controller\Adminhtml\Image;

use Amasty\PageSpeedTools\Model\OptionSource\Resolutions;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;

class ClearWebpFolder extends Action
{
    const ADMIN_RESOURCE = 'Amasty_ImageOptimizer::config';

    /**
     * @var ClearFolder
     */
    private $clearFolder;

    public function __construct(
        ClearFolder $clearFolder,
        Action\Context $context
    ) {
        parent::__construct($context);
        $this->clearFolder = $clearFolder;
    }

    public function execute()
    {
        try {
            $this->clearFolder->execute(Resolutions::WEBP_DIR);
            $this->messageManager->addSuccessMessage(__('WebP Images Folder was successful cleaned.'));
        } catch (LocalizedException $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
        }

        return $this->_redirect($this->_redirect->getRefererUrl());
    }
}
