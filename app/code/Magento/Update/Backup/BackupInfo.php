<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Update\Backup;

/**
 * Data object, which stores information about files to be archived.
 */
class BackupInfo
{
    /**
     * @var string
     */
    protected $blacklistFilePath;

    /**
     * @var string[]
     */
    protected $blacklist;

    /**
     * Init backup info
     *
     * @param string $blacklistFilePath
     */
    public function __construct($blacklistFilePath = null)
    {
        $this->blacklistFilePath = $blacklistFilePath ? $blacklistFilePath : __DIR__ . '/../etc/backup_blacklist.txt';
    }

    /**
     * Generate backup filename based on current timestamp.
     *
     * @return string
     */
    public function generateBackupFilename()
    {
        $currentDate = date('Y-m-d-H-i-s', time());
        return 'backup-' . $currentDate . '.zip';
    }

    /**
     * Return files/directories, which need to be excluded from backup
     *
     * @return string[]
     */
    public function getBlacklist()
    {
        if (null === $this->blacklist) {
            $blacklistContent = file_get_contents($this->blacklistFilePath);
            if ($blacklistContent === FALSE) {
                throw new \RuntimeException('Could not read the blacklist file: ' . $this->blacklistFilePath);
            }
            /** Ignore commented and empty lines */
            $blacklistArray = explode("\n", $blacklistContent);
            $blacklistArray = array_filter(
                $blacklistArray,
                function ($value) {
                    $value = trim($value);
                    return (empty($value) || strpos($value, '#') === 0) ? false : true;
                }
            );
            $this->blacklist = $blacklistArray;
        }
        return $this->blacklist;
    }

    /**
     * Return path to a directory, which need to be archived
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
        return UPDATER_BACKUP_DIR;
    }
}
