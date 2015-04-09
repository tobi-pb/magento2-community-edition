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
    /** @var \Magento\Update\Backup\BackupInfo */
    protected $backupInfo;

    /**
     * Initialize job instance.
     *
     * @param string $name
     * @param object $params
     * @param \Magento\Update\Status|null $jobStatus
     * @param \Magento\Update\Backup\BackupInfo|null $backupInfo
     */
    public function __construct($name, $params, \Magento\Update\Status $jobStatus = null, $backupInfo = null)
    {
        parent::__construct($name, $params, $jobStatus);
        $this->backupInfo = $backupInfo ? $backupInfo : new BackupInfo();
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $archivator = $this->createArchivator($this->backupInfo);
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
