<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Update;

use Magento\Update\Queue\Reader;
use Magento\Update\Queue\AbstractJob;
use Magento\Update\Queue\JobFactory;

/**
 * Class for access to the queue of Magento updater application jobs.
 */
class Queue
{
    /**#@+
     * Key used in queue file.
     */
    const KEY_JOBS = 'jobs';
    const KEY_JOB_NAME = 'name';
    const KEY_JOB_PARAMS = 'params';
    /**#@-*/

    /**
     * @var Reader
     */
    protected $reader;

    /**
     * @var JobFactory
     */
    protected $jobFactory;

    /**
     * Initialize dependencies.
     *
     * @param Reader|null $reader
     * @param JobFactory|null $jobFactory
     */
    public function __construct(Reader $reader = null, JobFactory $jobFactory = null)
    {
        $this->reader = $reader ? $reader : new Reader();
        $this->jobFactory = $jobFactory ? $jobFactory : new JobFactory();
    }

    /**
     * Pop all updater application queued jobs.
     * 
     * Note, that this method is not idempotent, queue will be cleared after its execution
     *
     * @return AbstractJob[]
     * @throws \RuntimeException
     */
    public function popQueuedJobs()
    {
        $jobs = [];
        $queue = json_decode($this->reader->read());
        if (!is_object($queue)) {
            return $jobs;
        }
        if (isset($queue->{self::KEY_JOBS}) && is_array($queue->{self::KEY_JOBS})) {
            /** @var object $job */
            foreach ($queue->{self::KEY_JOBS} as $job) {
                $this->validateJobDeclaration($job);
                $jobs[] = $this->jobFactory->create($job->{self::KEY_JOB_NAME}, (array)$job->{self::KEY_JOB_PARAMS});
            }
        } else {
            throw new \RuntimeException(sprintf('"%s" field is missing or is not an array.', self::KEY_JOBS));
        }
        $this->reader->clearQueue();
        return $jobs;
    }

    /**
     * Make sure job declaration is correct.
     *
     * @param object $job
     * @throws \RuntimeException
     */
    protected function validateJobDeclaration($job)
    {
        $requiredFields = [self::KEY_JOB_NAME, self::KEY_JOB_PARAMS];
        foreach ($requiredFields as $field) {
            if (!isset($job->{$field})) {
                throw new \RuntimeException(sprintf('"%s" field is missing for one or more jobs.', $field));
            }
        }
    }
}