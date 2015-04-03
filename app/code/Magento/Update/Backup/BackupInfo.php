<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Update\Backup;

class BackupInfo
{
    /** @var string */
    protected $blacklistFilename = 'backup_blacklist.txt';

    /** @var  array */
    protected $blacklist;

    /**
     * Init backup info
     *
     * @throws \Exception
     */
    public function __construct()
    {
        $this->blacklist = $this->readBlacklist();
    }

    /**
     * Return backup filename
     *
     * @return string
     */
    public function getBackupFilename()
    {
        date_default_timezone_set('UTC');
        $currentDate = date('Y-m-d-H-i-s', time());
        return 'backup-' . $currentDate . '.zip';
    }

    /**
     * Return files/directories, which need to be excluded from backup
     *
     * @return array
     */
    public function getBlacklist()
    {
        return $this->blacklist;
    }

    /**
     * Return absolute directory, which need to be archived
     *
     * @return string
     */
    public function getArchivedDirectory()
    {
        return MAGENTO_BP;
    }

    /**
     * Return path, where backup have to be saved
     *
     * @return string
     */
    public function getBackupPath()
    {
        return UPDATER_BP . '/var/backup';
    }

    /**
     * Read files/directories, which need to be excluded from backup
     *
     * @return array
     * @throws \Exception
     */
    protected function readBlacklist()
    {
        $blacklistPath = __DIR__ . '/../' . 'etc' . '/';
        $blacklistContent = file_get_contents($blacklistPath . $this->blacklistFilename);
        if ($blacklistContent === FALSE) {
            throw new \Exception('Could not read the blacklist file:' . $this->blacklistFilename);
        }
        $blacklistArray = explode("\n", $blacklistContent);
        $blacklistArray = array_filter(
            $blacklistArray,
            function ($value) {
                $value = trim($value);
                return (empty($value) || strpos($value, '#') === 0) ? false : true;
            }
        );
        return $blacklistArray;
    }
}
