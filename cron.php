<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

require_once __DIR__ . '/app/bootstrap.php';

$updateInProgressFlagFilename = UPDATER_BP . '/var/.update_in_progress.flag';
$backupDirectory = UPDATER_BP . '/var/backup';

if (file_exists($updateInProgressFlagFilename)) {
    exit('Cron is already in progress...');
}

$jobQueue = new \Magento\Update\Queue();
$jobStatus = new \Magento\Update\Status();

if (!file_exists($backupDirectory) && !mkdir($backupDirectory)) {
    $jobStatus->add(sprintf('Backup directory "%s" cannot be created.', $backupDirectory));
    exit();
}

$updateInProgressFlagFile = fopen($updateInProgressFlagFilename, 'w');
if (!$updateInProgressFlagFile) {
    $jobStatus->add(sprintf('"%s" cannot be created.', $updateInProgressFlagFilename));
    exit();
}

/** @var \Magento\Update\Queue\AbstractJob $job*/
foreach ($jobQueue->popQueuedJobs() as $job) {
    $jobStatus->add(
        sprintf('Job "%s" has been started with params: %s', $job->getName(), json_encode($job->getParams()))
    );
    try {
        $job->execute();
    } catch (\Exception $e) {
        $jobStatus->add(sprintf('An error occurred while executing job "%s": %s', $job->getName(), $e->getMessage()));
    }
    $jobStatus->add(sprintf('Job "%s" has been successfully completed', $job->getName()));
}
fclose($updateInProgressFlagFile);
if (file_exists($updateInProgressFlagFilename)) {
    unlink($updateInProgressFlagFilename);
}
