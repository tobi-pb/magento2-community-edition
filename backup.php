<?php

require_once __DIR__ . '/app/bootstrap.php';

// increase script timeout value
ini_set('max_execution_time', 5000);

$backupInfo = new \Magento\Update\Backup\BackupInfo();
$archivator = new \Magento\Update\Backup\UnixZipArchivator($backupInfo);
$backup = new \Magento\Update\Backup($backupInfo, $archivator);

echo $backup->run();