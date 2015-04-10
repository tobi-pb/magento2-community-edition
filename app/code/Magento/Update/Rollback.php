<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Update;

use Magento\Update\Status;

/**
 * Class for rollback capabilities
 */
class Rollback
{
    /**
     * @var string
     */
    protected $backupFileDir;

    /**
     * @var string
     */
    protected $restoreTargetDir;

    /**
     * @var string
     */
    protected $status;

    /**
     * Initialize rollback.
     *
     * @param string|null $backupFileDir
     * @param string|null $restoreTargetDir
     * @param Status|null $status
     */
    public function __construct($backupFileDir = null, $restoreTargetDir = null, Status $status = null)
    {
        $this->backupFileDir = $backupFileDir ? $backupFileDir : UPDATER_BACKUP_DIR;
        $this->restoreTargetDir = $restoreTargetDir ? $restoreTargetDir : MAGENTO_BP;
        $this->status = $status ? $status : new Status();
    }

    /**
     * Restore Magento code from the backup archive.
     *
     * Rollback to the code version stored in the specified backup archive.
     * If no archive specified, use the the most recent one.
     *
     * @param string|null $backupFilePath
     * @throws \RuntimeException
     * @return $this
     */
    public function execute($backupFilePath = null)
    {
        if (null === $backupFilePath) {
            $backupFilePath = $this->getLastBackupFilePath();
        }
        if (!file_exists($backupFilePath)) {
            throw new \RuntimeException(sprintf('"%s" backup file does not exist.', $backupFilePath));
        }
        $this->status->add(sprintf('Restoring archive from "%s" ...', $backupFilePath));
        $this->unzipArchive($backupFilePath);
        return $this;
    }

    /**
     * Find the last backup file from backup directory.
     *
     * @throws \RuntimeException
     * @return string
     */
    protected function getLastBackupFilePath()
    {
        $allFileList = scandir($this->backupFileDir, SCANDIR_SORT_DESCENDING);
        $backupFileName = '';

        foreach ($allFileList as $fileName) {
            if (strpos($fileName, 'backup') !== false) {
                $backupFileName = $fileName;
                break;
            }
        }

        if (empty($backupFileName)) {
            throw new \RuntimeException("No available backup file found.");
        }
        return $this->backupFileDir . $backupFileName;
    }

    /**
     * Unzip specified archive
     *
     * @param string $backupFilePath
     * @throws \RuntimeException
     * @return $this
     */
    protected function unzipArchive($backupFilePath)
    {
        $command = sprintf('unzip -o %s -d %s', $backupFilePath, $this->restoreTargetDir);
        exec($command, $output, $return);
        if ($return) {
            throw new \RuntimeException(
                sprintf('Error happened during execution of command "%s": %s', $command, implode("\n", $output))
            );
        }
        $this->status->add('Backup of Magento code was successfully created: "%s"', $backupFilePath);
        return $this;
    }
}
