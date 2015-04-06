<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Update\Queue;

use Magento\Update\RemoveBackup;

/**
 * Magento updater application 'remove_backups' job.
 */
class JobRemoveBackups extends AbstractJob
{
    const BACKUPS_FILE_NAMES = 'backups_file_names';

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $filesToDelete = [];
        if (isset($this->params[self::BACKUPS_FILE_NAMES])) {
            $filesToDelete = $this->params[self::BACKUPS_FILE_NAMES];
        }
        $removeBackup = new RemoveBackup($filesToDelete);
        $removeBackup->run();
        return $this;
    }
}
