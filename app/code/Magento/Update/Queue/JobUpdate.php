<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Update\Queue;

use Magento\Update\Queue\JobUpdate\ComposerManager;
use Magento\Update\Backup;
use Magento\Update\Rollback;
use Magento\Update\MaintenanceMode;

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
     * @param \Magento\Update\Status|null $status
     * @param \Magento\Update\Backup|null $backup
     * @param \Magento\Update\Rollback|null $rollback
     * @param \Magento\Update\Queue\JobUpdate\ComposerManager|null $composerManager
     * @param \Magento\Update\MaintenanceMode|null $maintenanceMode
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
            $this->status->add('Starting composer update...');
            foreach ($this->params as $directive => $params) {
                $this->composerManager->updateComposerConfigFile($directive, $params);
            }
            $this->composerManager->runUpdate();
            $this->status->setUpdateError(false);
            $this->maintenanceMode->set(false);
            $this->status->add('Composer update completed successfully');
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

    /**
     * Flash filesystem caches
     *
     * @return void
     */
    protected function flushMagentoCache()
    {
        $cacheDirs = [MAGENTO_BP . '/var', MAGENTO_BP . '/pub/static'];
        $blacklist = ['.', '..', '.htaccess'];

        $this->status->add('Flushing cache:');
        foreach ($cacheDirs as $cacheDir) {
            $elementsToRemove[] = array_diff(scandir($cacheDir), $blacklist);
            foreach ($elementsToRemove as $element) {
                $path = $cacheDir . '/' . $element;
                $this->status->add($path);
                if (is_dir($path)) {
                    exec('rm -rf ' . $path, $output, $return);
                    if ($return) {
                        $this->status->add(sprintf('Could not delete "%s", try to do it manually', $path));
                    }
                } else if (is_file($path)) {
                    if (!unlink($path)) {
                        $this->status->add(sprintf('Could not delete "%s", try to do it manually', $path));
                    }
                }
            }
        }
    }
}
