<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Cron;

use Amasty\Label\Api\Data\LabelInterface;
use Amasty\Label\Api\LabelRepositoryInterface;
use Amasty\Label\Model\ResourceModel\Label\Collection as LabelCollection;
use Amasty\Label\Model\ResourceModel\Label\CollectionFactory as LabelCollectionFactory;
use Amasty\Label\Model\Source\Status;

class ProcessLabelStatus
{
    /**
     * @var LabelCollectionFactory
     */
    private $labelCollectionFactory;

    /**
     * @var LabelRepositoryInterface
     */
    private $labelRepository;

    public function __construct(
        LabelCollectionFactory $labelCollectionFactory,
        LabelRepositoryInterface $labelRepository
    ) {
        $this->labelCollectionFactory = $labelCollectionFactory;
        $this->labelRepository = $labelRepository;
    }

    public function disableLabels(): void
    {
        $collection = $this->labelCollectionFactory->create();
        $collection->addFieldToFilter(LabelInterface::STATUS, Status::ACTIVE);
        $collection->addFieldToFilter(
            LabelInterface::ACTIVE_TO,
            ['lt' => new \Zend_Db_Expr('NOW()')]
        );
        $this->updateStatus($collection, Status::INACTIVE);
    }

    public function enableLabels(): void
    {
        $collection = $this->labelCollectionFactory->create();
        $collection->addFieldToFilter(LabelInterface::STATUS, Status::INACTIVE);
        $collection->addFieldToFilter(
            LabelInterface::ACTIVE_FROM,
            ['lt' => new \Zend_Db_Expr('NOW()')]
        );
        $collection->addFieldToFilter(
            LabelInterface::ACTIVE_TO,
            ['gt' => new \Zend_Db_Expr('NOW()')]
        );
        $this->updateStatus($collection, Status::ACTIVE);
    }

    public function updateStatus(LabelCollection $collection, int $status): void
    {
        /** @var LabelInterface $label **/
        foreach ($collection as $label) {
            $label->setStatus($status);
            $this->labelRepository->save($label);
        }
    }
}
