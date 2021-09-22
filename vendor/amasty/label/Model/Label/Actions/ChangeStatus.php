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

class ChangeStatus
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

    /**
     * @param int $labelId
     * @param int $status
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(int $labelId, int $status, bool $throwErrorIfNotExists = false): void
    {
        try {
            $label = $this->labelRepository->getById($labelId);
            $label->setStatus($status);
            $this->labelRepository->save($label);
        } catch (NoSuchEntityException $e) {
            if ($throwErrorIfNotExists) {
                throw $e;
            }
        }
    }
}
