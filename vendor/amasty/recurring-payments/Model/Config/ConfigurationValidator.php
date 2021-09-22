<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model\Config;

use Amasty\RecurringPayments\Api\Config\ValidatorInterface;

class ConfigurationValidator
{
    /** @var ValidatorInterface[]  */
    private $methodValidators = [];

    public function __construct(
        array $methodValidators = []
    ) {
        $this->methodValidators = $methodValidators;
    }

    public function isMethodConfiguredProperly(string $methodName): bool
    {
        foreach ($this->enumerateConfigurationIssues($methodName) as $issue) {
            return false; // Fail on first misconfiguration
        }

        return true;
    }

    public function getConfigurationIssues(string $methodName): array
    {
        return iterator_to_array($this->enumerateConfigurationIssues($methodName));
    }

    public function enumerateConfigurationIssues(string $methodName): \Generator
    {
        if (!isset($this->methodValidators[$methodName])) {
            yield __('Payment method adapter is not enabled');

            return;
        }

        foreach ($this->methodValidators[$methodName]->enumerateConfigurationIssues() as $issue) {
            yield $issue;
        }
    }
}
