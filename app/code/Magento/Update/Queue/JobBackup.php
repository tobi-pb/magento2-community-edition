<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Update\Queue;

use Magento\Update\Backup\BackupInfo;
use Magento\Update\Backup\PhpArchivator;
use Magento\Update\Backup\UnixZipArchivator;

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
        $this->jobStatus->add(sprintf('Backup %s has been created', $archivator->archive()));
        return $this;
    }

    /**
     * Return concrete archivator
     *
     * @param \Magento\Update\Backup\BackupInfo $backupInfo
     * @return \Magento\Update\Backup\ArchivatorInterface
     */
    protected function createArchivator($backupInfo)
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return new PhpArchivator($backupInfo);
        } else {
            return new UnixZipArchivator($backupInfo);
        }
    }
}
