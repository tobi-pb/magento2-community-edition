<?php

require_once __DIR__ . '/app/bootstrap.php';

// increase script timeout value
ini_set('max_execution_time', 5000);

$backupInfo = new \Magento\Update\Backup\BackupInfo();
$backup = new \Magento\Update\Backup($backupInfo);

echo $backup->run();