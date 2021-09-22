<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


namespace Amasty\Label\Ui\Component\Listing\Column;

use Amasty\Label\Api\Data\LabelFrontendSettingsInterface;
use Amasty\Label\Model\Label\Parts\FrontendSettings\GetLabelImageUrl;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Preview extends Column
{
    /**
     * @var GetLabelImageUrl
     */
    private $getLabelImageUrl;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        GetLabelImageUrl $getLabelImageUrl,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->getLabelImageUrl = $getLabelImageUrl;
    }

    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $image = $item[$this->getData('name')];
                if ($image) {
                    $config = $this->getData();
                    $mode = $config['config']['labelType'] ?? 'product';
                    $item[$this->getData('name')] = sprintf(
                        '<img src="%s" title="%s"/>',
                        $this->getLabelImageUrl->execute($image),
                        $item[$mode . '_' . LabelFrontendSettingsInterface::LABEL_TEXT] ?? ''
                    );
                }
            }
        }
        
        return $dataSource;
    }
}
