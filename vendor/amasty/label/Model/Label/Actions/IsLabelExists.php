<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Model\Label\Actions;

use Amasty\Label\Api\LabelRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class IsLabelExists
{
    /**
     * @var LabelRepositoryInterface
     */
    private $labelRepository;

    public function __construct(
        LabelRepositoryInterface $labelRepository
    ) {
        $this->labelRepository = $labelRepository;
    }

    public function check(int $labelId): bool
    {
        try {
            $label = $this->labelRepository->getById($labelId);

            return (bool) $label;
        } catch (NoSuchEntityException $e) {
            return false;
        }
    }
}
