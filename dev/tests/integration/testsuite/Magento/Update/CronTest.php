<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Update;

use Magento\Theme\Model\Resource\Theme\Customization\Update;
use Magento\Update\Backup\BackupInfo;

class CronTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $cronScript;

    /**
     * @var string
     */
    protected $backupToRollback;

    /**
     * @var string
     */
    protected $backupToRemoveA;

    /**
     * @var string
     */
    protected $backupToRemoveB;

    /**
     * @var \Magento\Update\Status
     */
    protected $status;

    public function __construct()
    {
        $this->cronScript = UPDATER_BP . '/cron.php';
        $this->backupToRollback = TESTS_TEMP_DIR . '/var/backup/BackupToRollback.zip';
        $this->backupToRemoveA = TESTS_TEMP_DIR . '/var/backup/BackupToRemoveA.zip';
        $this->backupToRemoveB = TESTS_TEMP_DIR . '/var/backup/BackupToRemoveB.zip';
        $this->status = new \Magento\Update\Status();
    }

    public function setup()
    {
        file_put_contents($this->backupToRollback, 'w');
        file_put_contents($this->backupToRemoveA, 'w');
        file_put_contents($this->backupToRemoveB, 'w');
    }

    public function tearDown()
    {
        $this->status->setUpdateInProgress(false);
        if (file_exists($this->backupToRollback)) {
            unlink($this->backupToRollback);
        }
        if (file_exists($this->backupToRemoveA)) {
            unlink($this->backupToRemoveA);
        }
        if (file_exists($this->backupToRemoveB)) {
            unlink($this->backupToRemoveB);
        }
        array_map('unlink', glob(UPDATER_BP . '/var/backup/*.zip'));
    }

    public function testUpdateInProgress()
    {
        $this->status->setUpdateInProgress();
        $actualResponse = shell_exec('php -f ' . $this->cronScript);
        $this->assertEquals('Cron is already in progress...', $actualResponse);
    }

    public function testValidQueue()
    {
        $this->assertTrue(file_exists($this->backupToRollback));
        $this->assertTrue(file_exists($this->backupToRemoveA));
        $this->assertTrue(file_exists($this->backupToRemoveB));
        $currentBackups = scandir(UPDATER_BP . '/var/backup/');

        file_put_contents(MAGENTO_BP . '/var/.update_queue.json',
            '{
              "jobs": [
                {
                  "name": "backup",
                  "params": {}
                },
                {
                  "name": "remove_backups",
                  "params": {
                    "backups_file_names": [
                      "' . $this->backupToRemoveA . '",
                      "' . $this->backupToRemoveB . '"
                    ]
                  }
                }
              ],
              "ignored_field": ""
            }');
        shell_exec('php -f ' . $this->cronScript);

        $jobStatus = $this->status->get();
        // verify new backup was created
        $updatedBackups = scandir(UPDATER_BP . '/var/backup/');
        $this->assertTrue(count($updatedBackups) > count($currentBackups));
        $this->assertContains('Job "<backup>[]" has been successfully completed', $jobStatus);

        // verify removals
        $this->assertNotContains('An error occurred while executing job "<remove_backups>"', $jobStatus);
        $this->assertContains('Job "<remove_backups>{"backups_file_names":["' .
            str_replace('/', '\\/', $this->backupToRemoveA) . '","'. str_replace('/', '\\/', $this->backupToRemoveB) .
            '"]}" has been successfully completed', $jobStatus);
        $this->assertFalse(file_exists($this->backupToRemoveA));
        $this->assertFalse(file_exists($this->backupToRemoveB));
    }

    /**
     * Test invalid queue file
     *
     * @expectedException /RuntimeException
     * @expectedExceptionMessage RuntimeException: Missing job params "params" field is missing for one or more jobs
     */
    public function testInvalidQueue()
    {
        file_put_contents(MAGENTO_BP . '/var/.update_queue.json',
            '{
              "jobs": [
                {
                  "name": "backup"
                }
              ],
              "ignored_field": ""
            }');
        shell_exec('php -f ' . $this->cronScript);
        $jobStatus = $this->status->get();
        $this->assertContains('"params" field is missing for one or more jobs', $jobStatus);
    }
}
