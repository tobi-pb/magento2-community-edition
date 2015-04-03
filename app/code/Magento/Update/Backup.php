<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Update;

class Backup
{
    /** @var  \Magento\Update\Backup\ArchivatorInterface */
    protected $archivator;

    public function __construct(
        \Magento\Update\Backup\BackupInfo $backupInfo,
        \Magento\Update\Backup\ArchivatorInterface $archivator = null
    ) {
        if (null === $archivator) {
            $archivator = new \Magento\Update\Backup\PhpArchivator($backupInfo);
        }
        $this->archivator = $archivator;
    }

    /**
     * Run backup process
     *
     * @throws \Exception
     */
    public function run()
    {
        $this->archivator->archive();
    }
}
