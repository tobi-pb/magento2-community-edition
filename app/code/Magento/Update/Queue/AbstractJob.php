<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Update\Queue;

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
     * Initialize job instance.
     *
     * @param string $name
     * @param object $params
     */
    public function __construct($name, $params)
    {
        $this->name = $name;
        $this->params = $params;
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
     * Execute job.
     *
     * @return $this
     */
    abstract public function execute();
}