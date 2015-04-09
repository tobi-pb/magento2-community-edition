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

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $backupPath = UPDATER_BP . '/var/backup/';
        $filesToDelete = [];
        if (isset($this->params[self::BACKUPS_FILE_NAMES])) {
            $filesToDelete = $this->params[self::BACKUPS_FILE_NAMES];
        }
        if (
            file_exists(UPDATER_BP . self::MAINTENANCE_FLAG_FILE) ||
            $this->jobStatus->isUpdateError()
        ) {
            throw new \Exception("Cannot remove archives while setup is in progress");
            return;
        }
        foreach ($filesToDelete as $file) {
            if (!file_exists($backupPath . $file) || !unlink($backupPath . $file)) {
                throw new \RuntimeException("Could not delete backup archive " . $file);
            } else {
                $this->jobStatus->add(sprintf('%s was deleted successfully.\n', $file));
            }
        }
    }
}
