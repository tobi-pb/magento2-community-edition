<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Update\Backup;

class UnixZipArchivator implements ArchivatorInterface
{
    /** @var  \Magento\Update\Backup\BackupInfo */
    protected $backupInfo;

    /**
     * Init archivator
     *
     * @param BackupInfo $backupInfo
     */
    public function __construct(\Magento\Update\Backup\BackupInfo $backupInfo)
    {
        $this->backupInfo = $backupInfo;
    }

    /**
     * Archive with unix zip tool
     *
     * {@inheritdoc}
     */
    public function archive()
    {
        $backupFileName = $this->backupInfo->getBackupFilename();
        $backupFilePath = $this->backupInfo->getBackupPath() . DIRECTORY_SEPARATOR . $backupFileName;

        $archivedDirectory = $this->backupInfo->getArchivedDirectory() . '/*';

        $excludedElements = '';
        foreach ($this->backupInfo->getBlacklist() as $excludedElement) {
            $elementPath = $this->backupInfo->getArchivedDirectory() . $excludedElement;
            $excludedElements .= is_dir($elementPath) ? "{$elementPath}\* " : "{$elementPath} ";
        }

        $shellArguments = sprintf(
            "-r %s %s -x %s",
            escapeshellarg($backupFilePath),
            escapeshellarg($archivedDirectory),
            escapeshellarg($excludedElements)
        );

        $command = 'zip ' . $shellArguments;
        $lastLineOfCommand = exec($command, $output, $return);
        if ($return) {
            throw new \Exception(
                sprintf('Could not backup with script %s : %s', $command, $lastLineOfCommand)
            );
        }
        return $backupFileName;
    }
}
