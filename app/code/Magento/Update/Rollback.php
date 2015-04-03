<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Update;

class Rollback
{
    const BACKUP_DIR = '../../../../var/backup/';
    const FILE_DIR = "../../../../..";
    const EXIT_COMMAND = 'quit';
    const INPUT_PATTERN = '/^([0-9]{4}-[0-9]{2}-[0-9]{2}-[0-9]{2}-[0-9]{2}-[0-9]{2})$/';

    /**
     * Manual rollback to a archive version specified by user
     */
    public function manualRollback()
    {
        $backupVersion = readline(
            "Enter the backup version you wish to restore in this format [yyyy-mm-dd-hh-mm-ss] or enter [quit] to exit:"
        );
        while (true) {
            if ($backupVersion == self::EXIT_COMMAND) {
                exit;
            } elseif (!preg_match(self::INPUT_PATTERN, $backupVersion)) {
                $backupVersion = readline("The version you entered is in the wrong format! Please re-enter:");
            } else {
                break;
            }
        }
        $backupFilePath = self::BACKUP_DIR . 'backup-' . $backupVersion . 'zip';
        echo "Restoring archive from $backupFilePath ...";
        $this->rollbackHelper($backupFilePath);
    }

    /**
     * Automatic rollback when any error happens during update process
     *
     * @return bool
     */
    public function autoRollback()
    {
        $backupFileName = $this->getLastBackupFile();
        $backupFilePath = self::BACKUP_DIR . $backupFileName;
        $this->rollbackHelper($backupFilePath);

        return true;
    }

    /**
     * Find the last backup file from var/backup
     *
     * @throws \Exception
     * @return string
     */
    protected function getLastBackupFile()
    {
        $allFileList = scandir(self::BACKUP_DIR);
        $backupFileList = [];

        foreach ($allFileList as $fileName) {
            if (strpos($fileName, 'backup') !== null) {
                $backupFileList[] = $fileName;
            }
        }

        if (empty($backupFileList)) {
            throw new \Exception ("No available backup file found.");
        }
        sort($backupFileList);
        return array_pop($backupFileList);
    }

    /**
     * Rollback
     *
     * @param string $backupFilePath
     * @return void
     */
    protected function rollbackHelper($backupFilePath)
    {
        echo shell_exec('unzip ' . $backupFilePath . ' -d ' . self::FILE_DIR);
    }
}
