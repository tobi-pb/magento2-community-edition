<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Update;

class RemoveBackup
{
    const MAINTENANCE_FLAG_FILE = '/var/.maintenance.flag';

    /**
     * @var string
     */
    protected $backupPath;

    /**
     * @var string[]
     */
    protected $filesToDelete;

    /**
     * @var \Magento\Update\Status
     */
    protected $status;

    /**
     * @param string[] $filesToDelete
     * @param \Magento\Update\Status|null $status
     */
    public function __construct($filesToDelete, $status = null)
    {
        $this->backupPath = UPDATER_BP . '/var/backup/';
        $this->filesToDelete = $filesToDelete;
        $this->status = $status;
    }

    /**
     * @return string status
     * @throws \Exception
     */
    public function run()
    {
        if (file_exists(UPDATER_BP . self::MAINTENANCE_FLAG_FILE)
            || $this->status->isUpdateError()
        ) {
            throw new \Exception("Cannot remove archives while setup is in progress");
            return;
        }
        $status = '';
        foreach ($this->filesToDelete as $file) {
            if (!file_exists($this->backupPath . $file) || !unlink($this->backupPath . $file)) {
                throw new \RuntimeException("Could not delete backup archive " . $file);
            } else {
                $status .= $file . " was deleted successfully.\n";
            }
        }
        return $status;
    }
}
