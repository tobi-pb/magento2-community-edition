<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Update;

use Magento\Update\Backup\UnixZipArchive;
use Magento\Update\Backup\BackupInfo;

/**
 * Class Backup
 */
class Backup
{
    /** @var \Magento\Update\Backup\ArchiveInterface */
    protected $archivator;

    /**
     *
     * @param \Magento\Update\Backup\BackupInfo|null $backupInfo
     * @param \Magento\Update\Backup\ArchiveInterface|null $archivator
     */
    public function __construct(
        \Magento\Update\Backup\BackupInfo $backupInfo = null,
        \Magento\Update\Backup\ArchiveInterface $archivator = null
    ) {
        $backupInfo = $backupInfo ? $backupInfo : new BackupInfo();
        $this->archivator = $archivator ? $archivator : $this->createArchivator($backupInfo);
    }

    /**
     * Run backup process
     *
     * @throws \Exception
     */
    public function execute()
    {
        return $this->archivator->archive();
    }

    /**
     * Return concrete archivator
     *
     * @param \Magento\Update\Backup\BackupInfo $backupInfo
     * @return \Magento\Update\Backup\ArchiveInterface
     */
    protected function createArchivator($backupInfo)
    {
        return new UnixZipArchive($backupInfo);
    }
}
