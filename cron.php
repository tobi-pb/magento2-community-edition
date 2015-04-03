<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

require_once __DIR__ . '/app/bootstrap.php';

$updateInProgressFlagFilename = UPDATER_BP . '/var/.update_in_progress.flag';

if (file_exists($updateInProgressFlagFilename)) {
    exit('Cron is already in progress...');
}

$updateInProgressFlagFile = fopen($updateInProgressFlagFilename, 'w');
if (!$updateInProgressFlagFile) {
    exit('Cron could not open ' . $updateInProgressFlagFilename);
}

/** @var \Magento\Update\Queue $jobQueue */
$jobQueue = new \Magento\Update\Queue();

/** @var \Magento\Update\Status $jobLog */
$jobLog = new \Magento\Update\Status();

/** @var \Magento\Update\Queue\AbstractJob $job*/
foreach ($jobQueue->popQueuedJobs() as $job) {
    try {
        $job->execute();
    } catch (Exception $e) {
        $jobLog->add(sprintf('An error occurred while executing job %s: %s', $job->getName(), $e->getMessage()));
    }
}
fclose($updateInProgressFlagFile);
if (file_exists($updateInProgressFlagFilename)) {
    unlink($updateInProgressFlagFilename);
}
