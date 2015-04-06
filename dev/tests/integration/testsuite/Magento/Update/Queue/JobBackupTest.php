<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Update\Queue;

class JobBackupTest extends \PHPUnit_Framework_TestCase
{
    /** @var  string */
    protected $backupPath;

    /** @var array */
    protected $dirList = [];

    protected function setUp()
    {
        parent::setUp();
        $this->backupPath = UPDATER_BP . '/var/backup/';
    }

    public function testArchive()
    {
        $this->markTestSkipped('MAGETWO-35283');
        $jobName = 'Backup';
        $jobStatus = new \Magento\Update\Status();
        $jobStatus->clear();
        $jobBackup = new \Magento\Update\Queue\JobBackup($jobName, [], $jobStatus);
        $this->dirList = scandir($this->backupPath);

        $jobBackup->execute();

        $tmpFiles = array_diff(scandir($this->backupPath), $this->dirList);
        $backupFile = array_pop($tmpFiles);

        $actualJobStatus = $jobStatus->get();
        $expectedJobStatus = $jobName . ': Backup ' . $backupFile . ' has been created';
        $this->assertEquals($expectedJobStatus, $actualJobStatus);

        if (file_exists($this->backupPath . $backupFile)) {
            unlink($this->backupPath . $backupFile);
        }
    }
}
