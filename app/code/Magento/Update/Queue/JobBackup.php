<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Update\Queue;

use Magento\Update\Backup\BackupInfo;
use Magento\Update\Backup\PhpZipArchive;
use Magento\Update\Backup\UnixZipArchive;

/**
 * Magento updater application 'backup' job.
 */
class JobBackup extends AbstractJob
{
    // TODO: Add job specific getters and there initialization based on construct params in scope of https://jira.corp.x.com/browse/MAGETWO-35312

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $backupInfo = new BackupInfo();
        $archivator = $this->createArchivator($backupInfo);
        $this->jobStatus->add(sprintf('%s: Backup %s has been created', $this->getName(), $archivator->archive()));
        return $this;
    }

    /**
     * Return concrete archivator
     *
     * @param \Magento\Update\Backup\BackupInfo $backupInfo
     * @return \Magento\Update\Backup\ArchiveInterface
     */
    protected function createArchivator($backupInfo)
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return new PhpZipArchive($backupInfo);
        } else {
            return new UnixZipArchive($backupInfo);
        }
    }
}
