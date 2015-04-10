<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Update\Queue;

use \Magento\Update\Queue\JobUpdate\ComposerManager;
use \Magento\Update\Backup;
use \Magento\Update\Rollback;
use \Magento\Update\MaintenanceMode;

/**
 * Magento updater application 'update' job.
 */
class JobUpdate extends AbstractJob
{
    /** @var \Magento\Update\Backup */
    protected $backup;

    /** @var \Magento\Update\Queue\JobRollback */
    protected $jobRollback;

    /** @var \Magento\Update\Queue\JobUpdate\ComposerManager */
    protected $composerManager;

    /** @var \Magento\Update\MaintenanceMode */
    protected $maintenanceMode;

    /**
     * Initialize job instance
     *
     * @param string $name
     * @param array $params
     * @param \Magento\Update\Status $status
     * @param \Magento\Update\Backup $backup
     * @param \Magento\Update\Rollback $rollback
     * @param \Magento\Update\Queue\JobUpdate\ComposerManager $composerManager
     * @param \Magento\Update\MaintenanceMode $maintenanceMode
     */
    public function __construct(
        $name,
        $params,
        \Magento\Update\Status $status = null,
        \Magento\Update\Backup $backup = null,
        \Magento\Update\Rollback $rollback = null,
        \Magento\Update\Queue\JobUpdate\ComposerManager $composerManager = null,
        \Magento\Update\MaintenanceMode $maintenanceMode = null
    ) {
        parent::__construct($name, $params, $status);
        $this->backup = $backup ? $backup : new Backup();
        $this->rollback = $rollback ? $rollback : new Rollback();
        $this->composerManager = $composerManager ? $composerManager : new ComposerManager();
        $this->maintenanceMode = $maintenanceMode ? $maintenanceMode : new MaintenanceMode();
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $this->maintenanceMode->set(true);
        $this->backup->execute();
        try {
            foreach ($this->params as $directive => $params) {
                $this->composerManager->updateComposerConfigFile($directive, $params);
            }
            $this->composerManager->runUpdate();
            $this->status->setUpdateError(false);
            $this->maintenanceMode->set(false);
        } catch (\Exception $e) {
            $this->status->setUpdateError(true);
            try {
                $this->rollback->execute();
                $this->status->setUpdateError(false);
                $this->maintenanceMode->set(false);
            } catch (\Exception $e) {
                throw new \RuntimeException(sprintf('Could not complete %s successfully: %s', $this, $e->getMessage()));
            }
            throw new \RuntimeException(sprintf('Could not complete %s successfully: %s', $this, $e->getMessage()));
        }
        $this->flushMagentoCache();
        return $this;
    }

    protected function flushMagentoCache()
    {
        //TODO: handle here cache flushing
    }
}
