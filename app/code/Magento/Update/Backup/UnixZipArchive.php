<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Update\Backup;

/**
 * Class for backup archive creating using command line zip utility.
 */
class UnixZipArchive implements ArchiveInterface
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
        $backupFileName = $this->backupInfo->generateBackupFilename();
        $backupFilePath = $this->backupInfo->getBackupPath() . '/' . $backupFileName;
        $archivedDirectory = $this->backupInfo->getArchivedDirectory() . '/*';
        $excludedElements = '';

        foreach ($this->backupInfo->getBlacklist() as $excludedElement) {
            $elementPath = $this->backupInfo->getArchivedDirectory() . $excludedElement;
            $excludedElements .= is_dir($elementPath) ? $elementPath . '\* '  : $elementPath . ' ';
        }
        $command = sprintf("zip -r %s %s -x %s", $backupFilePath, $archivedDirectory, $excludedElements);
        $lastLineOfOutput = exec($command, $output, $return);
        if ($return) {
            throw new \Exception(
                sprintf('Could not backup with script %s : %s', $command, $lastLineOfOutput)
            );
        }
        return $backupFileName;
    }
}
