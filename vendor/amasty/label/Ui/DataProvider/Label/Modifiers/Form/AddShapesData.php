<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Ui\DataProvider\Label\Modifiers\Form;

use Amasty\Label\Model\Label\Shape\DataProvider;
use Magento\Framework\Escaper;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

class AddShapesData implements ModifierInterface
{
    /**
     * @var array
     */
    private $sectionNames;

    /**
     * @var array
     */
    private $elementNames;

    /**
     * @var DataProvider
     */
    private $shapeDataProvider;

    /**
     * @var Escaper
     */
    private $escaper;

    public function __construct(
        DataProvider $shapeDataProvider,
        Escaper $escaper,
        $sectionNames = [],
        $elementNames = []
    ) {
        $this->sectionNames = $sectionNames;
        $this->elementNames = $elementNames;
        $this->shapeDataProvider = $shapeDataProvider;
        $this->escaper = $escaper;
    }

    public function modifyData(array $data): array
    {
        return $data;
    }

    public function modifyMeta(array $meta): array
    {
        foreach ($this->sectionNames as $sectionName) {
            if (isset($this->elementNames[$sectionName])) {
                $shapeList = $this->generateShapeList();
                $meta[$sectionName]['children'][$this->elementNames[$sectionName]]['arguments']
                ['data']['config']['shapeList'] = $shapeList;
            }
        }

        return $meta;
    }

    private function generateShapeList(): array
    {
        $result = [];

        foreach ($this->shapeDataProvider->getAllTypes() as $shapeType => $shapeDescription) {
            try {
                $result[] = [
                    'shapeType' => $this->escaper->escapeHtmlAttr($shapeType),
                    'shapeContent' => $this->shapeDataProvider->getContent($shapeType),
                    'shapeDescription' => $this->escaper->escapeHtml((string) $shapeDescription)
                ];
            } catch (\InvalidArgumentException $ex) {
                continue;
            }
        }

        return $result;
    }
}
