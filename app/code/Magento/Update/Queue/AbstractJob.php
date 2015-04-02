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
     * @var array
     */
    protected $params;

    /**
     * Initialize job instance.
     *
     * @param string $name
     * @param array $params
     */
    public function __construct($name, array $params)
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
}