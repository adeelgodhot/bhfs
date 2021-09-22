<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Model\Label\Save\Preprocessors;

use Amasty\Base\Model\Serializer;
use Amasty\Label\Api\Data\LabelInterface;
use Amasty\Label\Model\Label\Save\DataPreprocessorInterface;
use Amasty\Label\Model\LabelRegistry;
use Amasty\Label\Model\RuleFactory;

class ProcessRulesConditions implements DataPreprocessorInterface
{
    /**
     * @var RuleFactory
     */
    private $ruleFactory;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var LabelRegistry
     */
    private $labelRegistry;

    public function __construct(
        RuleFactory $ruleFactory,
        Serializer $serializer,
        LabelRegistry $labelRegistry
    ) {
        $this->ruleFactory = $ruleFactory;
        $this->serializer = $serializer;
        $this->labelRegistry = $labelRegistry;
    }

    public function process(array $data): array
    {
        if (isset($data['label_display_conditions']['rule']['conditions'])) {
            $condition = $data['label_display_conditions']['rule'];
            unset($data['label_display_conditions']);
            $rule = $this->ruleFactory->create();
            $rule->loadPost($condition);
            $data[LabelInterface::CONDITION_SERIALIZED] = $this->serializer->serialize(
                $rule->getConditions()->asArray()
            );
        } else {
            $existingConditions = $this->labelRegistry->getCurrentLabel()->getConditionSerialized();
            $data[LabelInterface::CONDITION_SERIALIZED] = $existingConditions;
        }

        return $data;
    }
}
