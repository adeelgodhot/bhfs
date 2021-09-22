<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Model\ResourceModel;

use Amasty\Label\Api\Data\LabelInterface;
use Amasty\Label\Model\ResourceModel\Label as LabelResource;
use Amasty\Label\Model\ResourceModel\Label\Save\AdditionalSaveActionInterface;
use Amasty\Label\Model\ResourceModel\Label\Save\AdditionalSaveActionsPool;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

class Label extends AbstractDb
{
    const TABLE_NAME = 'amasty_label_entity';

    /**
     * @var AdditionalSaveActionsPool
     */
    private $additionalSaveActionsPool;

    public function __construct(
        Context $context,
        AdditionalSaveActionsPool $additionalSaveActionsPool,
        $connectionName = null
    ) {
        parent::__construct(
            $context,
            $connectionName
        );
        $this->additionalSaveActionsPool = $additionalSaveActionsPool;
    }

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, LabelInterface::LABEL_ID);
    }

    protected function _afterSave(AbstractModel $object): LabelResource
    {
        /**
         * @var AdditionalSaveActionInterface
         * @var LabelInterface $object
         */
        foreach ($this->additionalSaveActionsPool as $action) {
            $action->execute($object);
        }

        return parent::_afterSave($object);
    }
}
