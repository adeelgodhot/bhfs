<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MWishlist
 */


declare(strict_types=1);

namespace Amasty\MWishlist\Model\Source\Email;

use Amasty\MWishlist\Model\ResourceModel\Template\Collection;

class Template extends \Magento\Framework\DataObject implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Email\Model\Template\Config
     */
    private $emailConfig;

    /**
     * @var string
     */
    private $origTemplateCode;

    /**
     * @var Collection
     */
    private $collection;

    public function __construct(
        \Magento\Email\Model\Template\Config $emailConfig,
        Collection $collection,
        $origTemplateCode = '',
        array $data = []
    ) {
        parent::__construct($data);
        $this->emailConfig = $emailConfig;
        $this->origTemplateCode = $origTemplateCode;
        $this->collection = $collection;
    }

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        $options = $this->collection->toOptionArray();
        array_unshift($options, $this->getDefaultTemplate());

        return $options;
    }

    /**
     * @return array
     */
    private function getDefaultTemplate(): array
    {
        $templateId = str_replace('/', '_', $this->getPath());
        $templateLabel = $this->emailConfig->getTemplateLabel($templateId);
        $templateLabel = __('%1 (Default)', $templateLabel);

        return ['value' => $templateId, 'label' => $templateLabel];
    }
}
