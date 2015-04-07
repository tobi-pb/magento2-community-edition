<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Update\Queue;

class JobBackupTest extends \PHPUnit_Framework_TestCase
{
    /** @var  string */
    protected $backupFilename;

    /** @var  string */
    protected $backupPath;

    /** @var array */
    protected $dirList = [];

    protected function setUp()
    {
        parent::setUp();
        $this->backupFilename = uniqid('test_backup') . '.zip';
        $this->backupPath = UPDATER_BP . '/var/backup/';
    }

    public function testArchive()
    {
        $jobName = 'Backup';
        $jobStatus = new \Magento\Update\Status();
        $jobStatus->clear();

        $backupInfo = $this->getMockBuilder('Magento\Update\Backup\BackupInfo')
            ->disableOriginalConstructor()
            ->setMethods(['getBackupFilename','getBlacklist', 'getArchivedDirectory'])
            ->getMock();
        $backupInfo->expects($this->any())
            ->method('getBackupFilename')
            ->willReturn($this->backupFilename);
        $backupInfo->expects($this->any())
            ->method('getArchivedDirectory')
            ->willReturn(UPDATER_BP);
        $backupInfo->expects($this->any())
            ->method('getBlacklist')
            ->willReturn(['/var/backup', '/vendor', '/app/code']);

        $jobBackup = new \Magento\Update\Queue\JobBackup($jobName, [], $jobStatus, $backupInfo);
        $this->dirList = scandir($this->backupPath);

        $jobBackup->execute();

        $tmpFiles = array_diff(scandir($this->backupPath), $this->dirList);
        $actualBackupFile = array_pop($tmpFiles);
        $this->assertEquals($this->backupFilename, $actualBackupFile);

        $actualJobStatus = $jobStatus->get();
        $expectedJobStatus = $jobName . ': Backup ' . $actualBackupFile . ' has been created';
        $this->assertRegExp('/' . $expectedJobStatus . '/', $actualJobStatus);

        if (file_exists($this->backupPath . $actualBackupFile)) {
            unlink($this->backupPath . $actualBackupFile);
        }
    }
}
