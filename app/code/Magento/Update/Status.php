<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Update;

/**
 * Class which provides access to the current status of the Magento updater application.
 *
 * Each job is using this class to share information about its current status.
 * Current status can be seen on the updater app web page.
 */
class Status
{
    /**
     * @var string
     */
    protected $statusFilePath;

    /**
     * Initialize.
     *
     * @param string|null $statusFilePath
     */
    public function __construct($statusFilePath = null)
    {
        $this->statusFilePath = $statusFilePath ? $statusFilePath : UPDATER_BP . '/var/.update_status.txt';
    }

    /**
     * Get current updater application status.
     *
     * The last N status lines only may be requested using $maxNumberOfLines argument.
     *
     * To avoid display area overflow due to having overly long lines, $lineLengthLimit can be used.
     * E.g. if some line is 2.3 times longer than $lineLengthLimit, it will account for 3 lines.
     *
     * @param int|null $maxNumberOfLines
     * @param int $lineLengthLimit
     * @return string
     */
    public function get($maxNumberOfLines = null, $lineLengthLimit = 120)
    {
        return 'Lorem ipsum dolor sit amet';
    }

    /**
     * Add status update.
     *
     * @param string $text
     * @return $this
     */
    public function add($text)
    {
        return $this;
    }

    /**
     * Clear current status.
     *
     * @return $this
     */
    public function clear()
    {
        return $this;
    }
}