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
     * Init  archivator
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
        //@todo implement functionality using shell command "zip"
        return 'backup-file-name.zip';
    }

}
