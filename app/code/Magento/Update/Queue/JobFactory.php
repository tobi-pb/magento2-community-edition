<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Update\Queue;

/**
 * Magento updater application job factory.
 */
class JobFactory
{
    /**#@+
     * Job name
     */
    const NAME_UPDATE = 'update';
    const NAME_BACKUP = 'backup';
    const NAME_ROLLBACK = 'rollback';
    const NAME_REMOVE_BACKUPS = 'remove_backups';
    /**#@-*/

    /**
     * Create job instance.
     *
     * @param string $name
     * @param array $params
     * @return AbstractJob
     */
    public function create($name, array $params)
    {
        switch ($name) {
            case self::NAME_UPDATE:
                return new JobUpdate($name, $params);
                break;
            case self::NAME_BACKUP:
                return new JobBackup($name, $params);
                break;
            case self::NAME_ROLLBACK:
                return new JobRollback($name, $params);
                break;
            case self::NAME_REMOVE_BACKUPS:
                return new JobRemoveBackups($name, $params);
                break;
            default:
                throw new \RuntimeException(
                    sprintf(
                        '"%1" job is not supported. The following jobs are supported: %2.',
                        $name,
                        implode(', ', self::getListOfSupportedJobs())
                    )
                );
        }
    }

    /**
     * Get list of jobs by updater application.
     *
     * @return string[]
     */
    public static function getListOfSupportedJobs()
    {
        return [self::NAME_UPDATE, self::NAME_BACKUP, self::NAME_ROLLBACK, self::NAME_REMOVE_BACKUPS];
    }
}