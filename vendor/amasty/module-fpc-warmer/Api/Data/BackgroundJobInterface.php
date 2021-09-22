<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Fpc
 */


namespace Amasty\Fpc\Api\Data;

interface BackgroundJobInterface
{
    const JOB_ID = 'job_id';
    const JOB_CODE = 'job_code';

    public function getJobId(): int;

    public function setJobId(int $jobId): BackgroundJobInterface;

    public function getJobCode(): string;

    public function setJobCode(string $jobCode): BackgroundJobInterface;
}
