<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Update\Queue;

use Magento\Update\RemoveBackup;

class JobRemoveBackupsTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Update\Queue\JobRemoveBackups */
    protected $jobRemoveBackup;

    /** @var string */
    protected $backupFilename;

    /** @var string */
    protected $backupPath;

    protected function setUp()
    {
        parent::setUp();
        $this->backupFilenameA = uniqid('test_backupA') . '.zip';
        $this->backupFilenameB = uniqid('test_backupB') . '.zip';
        $this->backupFilenameC = uniqid('test_backupC') . '.zip';
        $this->backupPath = UPDATER_BP . '/var/backup/';
    }

    protected function tearDown()
    {
        parent::tearDown();
        if (file_exists($this->backupPath . $this->backupFilenameA)) {
            unlink($this->backupPath . $this->backupFilenameA);
        }
        if (file_exists($this->backupPath . $this->backupFilenameB)) {
            unlink($this->backupPath . $this->backupFilenameB);
        }
        if (file_exists($this->backupPath . $this->backupFilenameC)) {
            unlink($this->backupPath . $this->backupFilenameC);
        }
        if (file_exists(UPDATER_BP . RemoveBackup::UPDATE_ERROR_FLAG_FILE)) {
            unlink(UPDATER_BP . RemoveBackup::UPDATE_ERROR_FLAG_FILE);
        }
        if (file_exists(UPDATER_BP . RemoveBackup::MAINTENANCE_FLAG_FILE)) {
            unlink(UPDATER_BP . RemoveBackup::MAINTENANCE_FLAG_FILE);
        }
    }

    /**
     * @dataProvider flagFileDataProvider
     * @expectedException \Exception
     * @expectedExceptionMessage Cannot remove archives while setup is in progress
     */
    public function testExecuteFlag($flag)
    {
        if (!file_exists($flag)) {
            file_put_contents($flag, '');
        }
        $this->jobRemoveBackup = new \Magento\Update\Queue\JobRemoveBackups(
            'remove_backups',
            [$this->backupFilenameA]
        );
        $this->jobRemoveBackup->execute();
    }

    public function flagFileDataProvider() {
        return [
            [UPDATER_BP . RemoveBackup::MAINTENANCE_FLAG_FILE],
            [UPDATER_BP . RemoveBackup::UPDATE_ERROR_FLAG_FILE]
        ];
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Could not delete backup archive
     */
    public function testExecuteInvalidBackupFile()
    {
        $this->jobRemoveBackup = new \Magento\Update\Queue\JobRemoveBackups(
            'remove_backups',
            ['backups_file_names' => ['no-such-file.zip']]
        );
        $this->jobRemoveBackup->execute();
    }

    public function testExecuteSingle()
    {
        if (!file_exists($this->backupPath . $this->backupFilenameA)) {
            file_put_contents($this->backupPath . $this->backupFilenameA, '');
        }
        $this->jobRemoveBackup = new \Magento\Update\Queue\JobRemoveBackups(
            'remove_backups',
            ['backups_file_names' => [$this->backupFilenameA]]
        );
        $this->jobRemoveBackup->execute();
        $this->assertFalse(file_exists($this->backupPath . $this->backupFilenameA));
    }

    public function testExecuteMultiple()
    {
        if (!file_exists($this->backupPath . $this->backupFilenameA)) {
            file_put_contents($this->backupPath . $this->backupFilenameA, '');
        }
        if (!file_exists($this->backupPath . $this->backupFilenameB)) {
            file_put_contents($this->backupPath . $this->backupFilenameB, '');
        }
        if (!file_exists($this->backupPath . $this->backupFilenameC)) {
            file_put_contents($this->backupPath . $this->backupFilenameC, '');
        }
        $this->jobRemoveBackup = new \Magento\Update\Queue\JobRemoveBackups(
            'remove_backups',
            [
                'backups_file_names' => [
                    $this->backupFilenameA,
                    $this->backupFilenameB
                ]
            ]
        );
        $this->jobRemoveBackup->execute();
        $this->assertFalse(file_exists($this->backupPath . $this->backupFilenameA));
        $this->assertFalse(file_exists($this->backupPath . $this->backupFilenameB));
        $this->assertTrue(file_exists($this->backupPath . $this->backupFilenameC));
    }
}
