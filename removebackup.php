<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

require_once __DIR__ . '/app/bootstrap.php';

$filesToDelete = [];

while (true) {
    $input = trim(readline("Please enter the file name of the backup to be deleted. "
            . "You may also enter [all] to remove all backups or [quit] to exit: "));
    if ($input == 'quit') {
        return;
    } elseif ($input == 'all') {
        if (readline("Are you sure you want to delete all backup files? [y/n] ") == 'y') {
            foreach (scandir(UPDATER_BP . '/var/backup/') as $file) {
                if ($file != '.' && $file != '..') {
                    $filesToDelete[] = $file;
                }
            }
            break;
        }
    } elseif (strlen($input) == 0) {
        print("No file name given\n");
    } elseif (!file_exists(UPDATER_BP . '/var/backup/' . $input)) {
        print(UPDATER_BP . '/var/backup/' . $input . " does not exist!\n");
    } else {
        $filesToDelete = [$input];
        break;
    }
}

$removeBackup = new \Magento\Update\RemoveBackup($filesToDelete);
try {
    echo $removeBackup->run();
} catch (\Exception $e) {
    echo $e->getMessage();
}
