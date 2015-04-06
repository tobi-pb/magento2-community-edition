<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Update\Queue;

use \Magento\Update\Status;

/**
 * Magento updater application abstract job.
 */
abstract class AbstractJob
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var object
     */
    protected $params;

    /**
     * @var \Magento\Update\Status
     */
    protected $jobStatus;

    /**
     * Initialize job instance.
     *
     * @param string $name
     * @param object $params
     * @param \Magento\Update\Status $jobStatus
     */
    public function __construct($name, $params, $jobStatus = null)
    {
        $this->name = $name;
        $this->params = $params;
        $this->jobStatus = $jobStatus ? $jobStatus : new Status();
    }

    /**
     * Get job name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get job params.
     *
     * @return object
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Execute job.
     *
     * @return $this
     */
    abstract public function execute();
}