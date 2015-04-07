<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Update\Backup;

class PhpZipArchive implements ArchiveInterface
{
    /** @var  \Magento\Update\Backup\BackupInfo */
    protected $backupInfo;

    /**
     * Init  archivator
     *
     * @param BackupInfo $backupInfo
     */
    public function __construct(\Magento\Update\Backup\BackupInfo $backupInfo)
    {
        $this->backupInfo = $backupInfo;
    }

    /**
     * Archive with built in PHP ZipArchive
     *
     * @throws \Exception
     */
    public function archive()
    {
        $zip = new \ZipArchive();

        $backupFileName = $this->backupInfo->getBackupFilename();
        $backupFilePath = $this->backupInfo->getBackupPath() . DIRECTORY_SEPARATOR . $backupFileName;

        if ($zip->open($backupFilePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \Exception("Could not open archive for backup");
        }

        $iterator = $this->getRecursiveDirectoryIterator();

        foreach ($iterator as $key => $value) {
            $relativePath = str_replace($this->backupInfo->getArchivedDirectory() . DIRECTORY_SEPARATOR, '', $key);
            if (is_dir($key)) {
                if ($zip->addEmptyDir($relativePath) !== true) {
                    throw new \Exception ("Could not add dir: $key");
                }
            } elseif (is_file($key)) {
                if ($zip->addFile(realpath($key), $relativePath) !== true) {
                    throw new \Exception ("Could not add file: $key");
                }
            }
        }
        $zip->close();
        return $backupFileName;
    }

    /**
     * Return recursive directory iterator with blacklist processing
     *
     * @return \RecursiveIteratorIterator
     */
    protected function getRecursiveDirectoryIterator()
    {
        $rdIterator = new \RecursiveDirectoryIterator(
            $this->backupInfo->getArchivedDirectory(),
            \FilesystemIterator::SKIP_DOTS
        );
        $filterIterator = new PhpArchivator\BlacklistFilterIterator(
            $rdIterator,
            $this->backupInfo->getArchivedDirectory(),
            $this->backupInfo->getBlacklist()
        );
        $iterator = new \RecursiveIteratorIterator($filterIterator);
        return $iterator;
    }
}
