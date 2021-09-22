<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Ui\DataProvider\Label\Modifiers\Form;

use Amasty\Label\Model\LabelRegistry;

trait LabelSpecificSettingsTrait
{
    /**
     * @var LabelRegistry
     */
    private $labelRegistry;

    public function modifyMeta(array $meta): array
    {
        return $meta;
    }

    public function modifyData(array $data): array
    {
        $label = $this->labelRegistry->getCurrentLabel();

        if ($label !== null) {
            $labelId = $label->getLabelId();

            if (isset($data[$labelId])) {
                $data = $this->executeIfLabelExists($labelId, $data);
            }
        }

        return $data;
    }

    abstract protected function executeIfLabelExists(int $labelId, array $data): array;
}
