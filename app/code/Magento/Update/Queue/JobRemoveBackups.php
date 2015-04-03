<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Update\Queue;

/**
 * Magento updater application 'remove_backups' job.
 */
class JobRemoveBackups extends AbstractJob
{
    const BACKUPS_FILE_NAMES = 'backups_file_names';
    const MAINTENANCE_FLAG_FILE = '/var/.maintenance.flag';
    const UPDATE_ERROR_FLAG_FILE = '/var/.update_error.flag';

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        if (file_exists(UPDATER_BP . self::MAINTENANCE_FLAG_FILE) ||
            file_exists(UPDATER_BP . self::UPDATE_ERROR_FLAG_FILE)) {
            throw new \Exception("Cannot remove archives while setup is in progress");
            return;
        }
        $filesToDelete = [];
        if (isset($this->params[self::BACKUPS_FILE_NAMES])) {
            $filesToDelete = $this->params[self::BACKUPS_FILE_NAMES];
        }
        foreach ($filesToDelete as $file) {
            if (!file_exists($file) || !unlink($file)) {
                throw new \Exception("Could not delete backup archive " . $file);
            }
        }
        return $this;
    }
}