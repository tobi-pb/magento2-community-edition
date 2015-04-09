<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Update;

class RollbackTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Update\Rollback
     */
    protected $rollBack;

    /**
     * @var string
     */
    protected $backupPath;

    /**
     * @var string
     */
    protected $archivedDir;

    protected function setup()
    {
        parent::setUp();
        $this->backupPath = UPDATER_BP . '/dev/tests/integration/testsuite/Magento/Update/_files/backup/';
        $this->archivedDir = UPDATER_BP . '/dev/tests/integration/testsuite/Magento/Update/_files/archived/';
        $this->rollBack = new \Magento\Update\Rollback($this->backupPath, $this->archivedDir);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage No available backup file found.
     */
    public function testAutoRollbackBackupFileUnavailable()
    {
        $this->rollBack->autoRollback();
    }

    public function testAutoRollback()
    {
        // Setup
        $backupFileName = uniqid('test_backup') . '.zip';
        $this->autoRollbackHelper();

        $backupInfo = $this->getMockBuilder('Magento\Update\Backup\BackupInfo')
            ->disableOriginalConstructor()
            ->setMethods(['getBackupFilename', 'getBackupPath', 'getBlacklist', 'getArchivedDirectory'])
            ->getMock();
        $backupInfo->expects($this->any())
            ->method('getBackupFilename')
            ->willReturn($backupFileName);
        $backupInfo->expects($this->any())
            ->method('getBackupPath')
            ->willReturn($this->backupPath);
        $backupInfo->expects($this->any())
            ->method('getArchivedDirectory')
            ->willReturn($this->archivedDir);
        $backupInfo->expects($this->any())
            ->method('getBlacklist')
            ->willReturn([]);

        $archivator = new \Magento\Update\Backup\UnixZipArchive($backupInfo);
        $result = $archivator->archive();
        $this->assertEquals($backupFileName, $result);

        // Change the contents of a.txt
        $this->autoRollbackHelper(1);
        $this->assertEquals('foo changed', file_get_contents($this->archivedDir . 'a.txt'));

        // Rollback process
        $this->rollBack->autoRollback();

        // Assert that the contents of a.txt has been restored properly
        $this->assertEquals('foo', file_get_contents($this->archivedDir . 'a.txt'));

        // Tear down
        $this->autoRollbackHelper(2);
        unlink($this->backupPath . $backupFileName);
    }

    /**
     * Helper to create simple files
     *
     * @param int $flag
     */
    protected function autoRollbackHelper($flag = 0)
    {
        $fileA = 'a.txt';
        $fileB = 'b.txt';
        $fileC = 'c.txt';

        if ($flag === 0) {
            file_put_contents($this->archivedDir . $fileA, 'foo');
            file_put_contents($this->archivedDir . $fileB, 'bar');
            file_put_contents($this->archivedDir . $fileC, 'baz');
        } elseif ($flag === 1) {
            file_put_contents($this->archivedDir . $fileA, 'foo changed');
        } elseif ($flag === 2) {
            unlink($this->archivedDir . $fileA);
            unlink($this->archivedDir . $fileB);
            unlink($this->archivedDir . $fileC);
        }
    }
}
