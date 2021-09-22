<?php
/**
 * Copyright © Mageside. All rights reserved.
 * See MS-LICENSE.txt for license details.
 */

namespace Mageside\Recipe\Observer;

use Mageside\Recipe\Model\WriterFactory;
use Mageside\Recipe\Model\FileUploader;
use Magento\Framework\Filter\FilterManager;

/**
 * Class WriterSave
 * @package Mageside\Recipe\Model\Plugin
 */
class WriterSave implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var array
     */
    protected $availableImages = ['avatar', 'writer_image'];

    /**
     * @var WriterFactory
     */
    protected $writerModelFactory;

    /**
     * @var FileUploader
     */
    protected $fileUploader;

    /**
     * @var \Magento\Framework\Filter\FilterManager
     */
    protected $filter;

    /**
     * WriterSave constructor.
     * @param FileUploader $fileUploader
     * @param WriterFactory $writerModel
     * @param FilterManager $filter
     */
    public function __construct(
        FileUploader $fileUploader,
        WriterFactory $writerModel,
        FilterManager $filter
    ) {
        $this->fileUploader = $fileUploader;
        $this->writerModelFactory = $writerModel;
        $this->filter = $filter;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Customer\Api\Data\CustomerInterface $customer */
        $customer = $observer->getEvent()->getCustomer();
        /** @var \Magento\Framework\App\RequestInterface $request */
        $request = $observer->getEvent()->getRequest();

        if (!$customerId = $customer->getId()) {
            return $this;
        }

        $data = $request->getPost('customer');
        if (empty($data['writer'])) {
            return $this;
        }

        $dataWriter = $data['writer'];
        $dataWriter['customer_id'] = $customerId;

        foreach ($this->availableImages as $imageName) {
            if (!empty($dataWriter[$imageName])) {
                $image = $dataWriter[$imageName][0]['name'];
                $this->fileUploader->moveFileFromTmp($image);
                unset($dataWriter[$imageName]);
                $dataWriter[$imageName] = $image;
            } else {
                $dataWriter[$imageName] = '';
            }
        }

        $urlKey = isset($dataWriter['writer_url_key']) ? trim($dataWriter['writer_url_key']) : false;
        if (!empty($urlKey)) {
            $urlKey = $this->formatUrlKey($urlKey);
        } else {
            $urlKey = $this->formatUrlKey($customer->getFirstname() . '-' . $customer->getLastname());
        }

        /** @var \Mageside\Recipe\Model\Writer $model */
        $model = $this->writerModelFactory->create();
        $model->load($customerId, 'customer_id');

        $isDuplicateSaved = false;
        $count = 0;
        do {
            $newUrlKey = $urlKey;
            if ($count >= 1) {
                $newUrlKey = $urlKey . '-' . $count;
            }

            $dataWriter['writer_url_key'] = $newUrlKey;

            try {
                $count++;
                $model->addData($dataWriter);
                $model->save();
                $isDuplicateSaved = true;
            } catch (\Exception $e) {}
        } while (!$isDuplicateSaved);

        return $this;
    }

    /**
     * @param $str
     * @return string
     */
    public function formatUrlKey($str)
    {
        return $this->filter->translitUrl($str);
    }
}
