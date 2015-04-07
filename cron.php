<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

require_once __DIR__ . '/app/bootstrap.php';

$status = new \Magento\Update\Status();
if ($status->isUpdateInProgress()) {
    exit('Cron is already in progress...');
}

$backupDirectory = UPDATER_BP . '/var/backup';
if (!file_exists($backupDirectory) && !mkdir($backupDirectory)) {
    $status->add(sprintf('Backup directory "%s" cannot be created.', $backupDirectory));
    exit();
}

try {
    $status->setUpdateInProgress();
} catch (\RuntimeException $e) {
    $status->add($e->getMessage());
    exit();
}

$jobQueue = new \Magento\Update\Queue();
foreach ($jobQueue->popQueuedJobs() as $job) {
    $status->add(
        sprintf('Job "%s" has been started with params: %s', $job->getName(), json_encode($job->getParams()))
    );
    try {
        $job->execute();
    } catch (\Exception $e) {
        $status->add(sprintf('An error occurred while executing job "%s": %s', $job->getName(), $e->getMessage()));
    }
    $status->add(sprintf('Job "%s" has been successfully completed', $job->getName()));
}
$status->setUpdateInProgress(false);
