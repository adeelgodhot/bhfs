<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


namespace Amasty\Label\Controller\Adminhtml\Label;

use Amasty\Label\Model\Label;

class MassDelete extends MassActionAbstract
{
    /**
     * {@inheritdoc}
     */
    protected function getSuccessMessage($collectionSize = 0)
    {
        return __('A total of %1 record(s) have been deleted.', $collectionSize);
    }

    /**
     * @param Label $label
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    protected function itemAction(Label $label): void
    {
        $this->labelRepository->delete($label);
    }
}
