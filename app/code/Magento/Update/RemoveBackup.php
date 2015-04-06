<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Update;

class RemoveBackup
{
    const MAINTENANCE_FLAG_FILE = '/var/.maintenance.flag';
    const UPDATE_ERROR_FLAG_FILE = '/var/.update_error.flag';

    protected $backupPath;
    protected $filesToDelete;

    /**
     * @param string[] $filesToDelete
     */
    public function __construct($filesToDelete)
    {
        $this->backupPath = UPDATER_BP . '/var/backup/';
        $this->filesToDelete = $filesToDelete;
    }

    /**
     * @return string status
     * @throws \Exception
     */
    public function run()
    {
        if (file_exists(UPDATER_BP . self::MAINTENANCE_FLAG_FILE) ||
            file_exists(UPDATER_BP . self::UPDATE_ERROR_FLAG_FILE)) {
            throw new \Exception("Cannot remove archives while setup is in progress");
            return;
        }
        $status = '';
        foreach ($this->filesToDelete as $file) {
            if (!file_exists($this->backupPath . $file) || !unlink($this->backupPath . $file)) {
                throw new \Exception("Could not delete backup archive " . $file);
            } else {
                $status .= $file . " was deleted successfully.\n";
            }
        }
        return $status;
    }
}
