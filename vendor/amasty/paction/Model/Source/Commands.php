<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Paction
 */

declare(strict_types=1);

namespace Amasty\Paction\Model\Source;

use Amasty\Paction\Model\CommandResolver;
use Magento\Framework\Data\OptionSourceInterface;

class Commands implements OptionSourceInterface
{
    /**
     * @var CommandResolver
     */
    protected $commandResolver;

    /**
     * @var array
     */
    protected $types = [
        '',
        'addcategory',
        'removecategory',
        'replacecategory',
        '',
        'modifycost',
        'modifyprice',
        'modifyspecial',
        'modifyallprices',
        'updateadvancedprices',
        'addspecial',
        'addprice',
        'addspecialbycost',
        '',
        'related',
        'upsell',
        'crosssell',
        '',
        'unrelated',
        'unupsell',
        'uncrosssell',
        '',
        'copyrelate',
        'copyupsell',
        'copycrosssell',
        '',
        'copyoptions',
        'removeoptions',
        'copyattr',
        'copyimg',
        'removeimg',
        '',
        'changeattributeset',
        'changevisibility',
        '',
        'amdelete',
        '',
        'appendtext',
        'replacetext',
        ''
    ];

    public function __construct(
        CommandResolver $commandResolver
    ) {
        $this->commandResolver = $commandResolver;
    }

    public function toOptionArray()
    {
        $options = [];

        foreach ($this->types as $i => $type) {
            $data = $this->commandResolver->getCommandDataByName($type);
            $options[] = [
                'value' => $type ?? $i,
                'label' => __($data['label']),
            ];
        }

        return $options;
    }
}
