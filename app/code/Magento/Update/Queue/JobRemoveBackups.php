<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Update\Queue;

use Magento\Update\MaintenanceMode;

/**
 * Magento updater application 'remove_backups' job.
 */
class JobRemoveBackups extends AbstractJob
{
    const BACKUPS_FILE_NAMES = 'backups_file_names';

    /**
     * @var MaintenanceMode
     */
    protected $maintenanceMode;

    /**
     * Initialize job instance.
     *
     * @param string $name
     * @param array $params
     * @param \Magento\Update\Status|null $status
     * @param MaintenanceMode|null $maintenanceMode
     */
    public function __construct(
        $name,
        array $params,
        \Magento\Update\Status $status = null,
        MaintenanceMode $maintenanceMode = null
    ) {
        parent::__construct($name, $params, $status);
        $this->maintenanceMode = $maintenanceMode ? $maintenanceMode : new MaintenanceMode();
    }
    
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $filesToDelete = [];
        if (isset($this->params[self::BACKUPS_FILE_NAMES])) {
            $filesToDelete = $this->params[self::BACKUPS_FILE_NAMES];
        }
        if ($this->maintenanceMode->isOn() || $this->status->isUpdateError()) {
            throw new \RuntimeException("Cannot remove backup archives while setup is in progress.");
        }
        foreach ($filesToDelete as $archivePath) {
            if (file_exists($archivePath) && unlink($archivePath)) {
                $this->status->add(sprintf('"%s" was deleted successfully.', $archivePath));
            } else {
                throw new \RuntimeException(sprintf('Could not delete backup archive "%s"', $archivePath));
            }
        }
    }
}
