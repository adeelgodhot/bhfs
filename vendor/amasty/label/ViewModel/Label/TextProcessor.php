<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\ViewModel\Label;

use Amasty\Label\Api\Data\LabelInterface;
use Amasty\Label\Model\ConfigProvider;
use Amasty\Label\Model\Label\Text\ProcessorInterface;
use Amasty\Label\Model\Label\Text\VariableProcessorInterface;
use Amasty\Label\Model\Label\Text\ZeroValueCheckerInterface;
use Magento\Framework\Escaper;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class TextProcessor implements ArgumentInterface
{
    const SORT_ORDER = 'sortOrder';
    const PROCESSOR = 'processor';

    /**
     * @var string[]
     */
    private $allowedTags = [
        'b', 'big', 'br', 'center',
        'i', 'link', 'small', 'sub',
        'strong', 'sup', 'del', 's',
        'strike', 'u', 'tt', 'a'
    ];

    /**
     * @var ProcessorInterface[]
     */
    private $processors;

    /**
     * @var VariableProcessorInterface
     */
    private $variableProcessor;

    /**
     * @var ProcessorInterface
     */
    private $defaultProcessor;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var ZeroValueCheckerInterface
     */
    private $zeroValueChecker;

    /**
     * @var Escaper
     */
    private $escaper;

    public function __construct(
        VariableProcessorInterface $variableProcessor,
        ProcessorInterface $defaultProcessor,
        ConfigProvider $configProvider,
        ZeroValueCheckerInterface $zeroValueChecker,
        Escaper $escaper,
        array $processorConfig = [],
        array $allowedTags = []
    ) {
        $this->processors = $this->parseConfig($processorConfig);
        $this->variableProcessor = $variableProcessor;
        $this->defaultProcessor = $defaultProcessor;
        $this->configProvider = $configProvider;
        $this->zeroValueChecker = $zeroValueChecker;
        $this->escaper = $escaper;
        $this->allowedTags = array_merge($this->allowedTags, $allowedTags);
    }

    public function renderLabelText(?string $text, LabelInterface $label, bool $escape = false): ?string
    {
        $product = $label->getExtensionAttributes()->getRenderSettings()->getProduct();

        if ($text !== null) {
            $variables = $this->variableProcessor->extractVariables($text);

            foreach ($variables as $variable) {
                $variableProcessor = $this->processors[$variable] ?? $this->defaultProcessor;
                $variableValue = $variableProcessor->getVariableValue(
                    $variable,
                    $label,
                    $product
                );

                if ($this->configProvider->isHideLabelWithZeroValue()) {
                    /**
                     * The variable processor can itself check the value
                     * for zero like value if it implements the interface
                     * @see \Amasty\Label\Model\Label\Text\ZeroValueCheckerInterface
                     */
                    $isZeroValue = $variableProcessor instanceof ZeroValueCheckerInterface
                        ? $variableProcessor->isZeroValue($variableValue, $label)
                        : $this->zeroValueChecker->isZeroValue($variableValue, $label);

                    if ($isZeroValue) {
                        $label->getExtensionAttributes()->getRenderSettings()->setIsLabelVisible(false);

                        break;
                    }
                }

                $text = $this->variableProcessor->insertVariable(
                    $text,
                    $variable,
                    $variableValue
                );
            }
        }

        if ($escape) {
            $text = $this->escaper->escapeHtml($text, $this->allowedTags);
        }

        return $text;
    }

    /**
     * Parse config for variables renders.
     * If two renderers can process same variable renderer with higher sortOrder will be used
     *
     * @param array $processorConfig
     * @return array
     */
    private function parseConfig(array $processorConfig): array
    {
        $result = [];
        usort($processorConfig, function (array $configA, array $configB): int {
            $sortOrderA = $configA[self::SORT_ORDER] ?? 0;
            $sortOrderB = $configB[self::SORT_ORDER] ?? 0;

            return $sortOrderA <=> $sortOrderB;
        });

        foreach ($processorConfig as $config) {
            $processor = $config[self::PROCESSOR] ?? null;

            if ($processor instanceof ProcessorInterface) {
                foreach ($processor->getAcceptableVariables() as $variable) {
                    $result[$variable] = $processor;
                }
            }
        }

        return $result;
    }
}
