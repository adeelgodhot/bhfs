<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Ui\DataProvider\Label\Modifiers\Form;

use Amasty\Label\Model\LabelRegistry;
use Amasty\Label\Model\ResourceModel\Label\GetRelatedEntitiesIds as GetStoreIdsByLabelId;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

class AddStoresData implements ModifierInterface
{
    use LabelSpecificSettingsTrait;

    const DATA_SCOPE = 'stores';

    /**
     * @var GetStoreIdsByLabelId
     */
    private $getStoreIdsByLabelId;

    /**
     * @var LabelRegistry
     */
    private $labelRegistry;

    public function __construct(
        GetStoreIdsByLabelId $getStoreIdsByLabelId,
        LabelRegistry $labelRegistry
    ) {
        $this->getStoreIdsByLabelId = $getStoreIdsByLabelId;
        $this->labelRegistry = $labelRegistry;
    }

    protected function executeIfLabelExists(int $labelId, array $data): array
    {
        $storeIds = $this->getStoreIdsByLabelId->execute($labelId);
        $labelData = $data[$labelId] ?? [];
        $labelData[self::DATA_SCOPE] = join(',', $storeIds);
        $data[$labelId] = $labelData;

        return $data;
    }
}
