<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */


namespace Amasty\Followup\Model\Factories;

use \Amasty\Segments\Model\ResourceModel\Customer\Collection;

class SegmentFactory
{

    const AMASTY_SEGMENTS_SEGMENT_REPOSITORY_MODEL_PATH = '\Amasty\Segments\Model\SegmentRepository';
    const AMASTY_SEGMENTS_SEGMENT_COLLECTION_MODEL_PATH = '\Amasty\Segments\Model\ResourceModel\Segment\Collection';

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * SegmentFactory constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
    ){
        $this->objectManager = $objectManager;
    }

    /**
     * @return mixed
     */
    public function getSegmentRepository()
    {
        return $this->objectManager->create(self::AMASTY_SEGMENTS_SEGMENT_REPOSITORY_MODEL_PATH);
    }

    /**
     * @return mixed
     */
    public function getSegmentCollection()
    {
        return $this->objectManager->create(self::AMASTY_SEGMENTS_SEGMENT_COLLECTION_MODEL_PATH);
    }

    /**
     * @return string
     */
    public function getValidationField()
    {
        return Collection::AMASTY_SEGMENTS_INDEX_TABLE_CUSTOMER_FIELD_NAME;
    }
}
