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

    /**
     * @var string
     */
    protected $excludedDir;

    /**
     * @var string
     */
    protected $backupFileName;

    protected function setup()
    {
        parent::setUp();
        $this->backupPath = UPDATER_BP . '/dev/tests/integration/testsuite/Magento/Update/_files/backup/';
        $this->archivedDir = UPDATER_BP . '/dev/tests/integration/testsuite/Magento/Update/_files/archived/';
        $this->excludedDir = UPDATER_BP . '/dev/tests/integration/testsuite/Magento/Update/_files/archived/excluded/';
        mkdir($this->backupPath);
        mkdir($this->archivedDir);
        mkdir($this->excludedDir);
        $this->backupFileName = uniqid('test_backup') . '.zip';
        $this->rollBack = new \Magento\Update\Rollback($this->backupPath);
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->autoRollbackHelper(2);
        if (file_exists($this->backupPath . $this->backupFileName)) {
            unlink($this->backupPath . $this->backupFileName);
        }

        rmdir($this->backupPath);
        rmdir($this->excludedDir);
        rmdir($this->archivedDir);
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
        $this->autoRollbackHelper();

        $backupInfo = $this->getMockBuilder('Magento\Update\Backup\BackupInfo')
            ->disableOriginalConstructor()
            ->setMethods(['generateBackupFilename', 'getBackupPath', 'getBlacklist', 'getArchivedDirectory'])
            ->getMock();
        $backupInfo->expects($this->any())
            ->method('generateBackupFilename')
            ->willReturn($this->backupFileName);
        $backupInfo->expects($this->any())
            ->method('getBackupPath')
            ->willReturn($this->backupPath);
        $backupInfo->expects($this->any())
            ->method('getArchivedDirectory')
            ->willReturn($this->archivedDir);
        $backupInfo->expects($this->any())
            ->method('getBlacklist')
            ->willReturn([$this->excludedDir]);

        $archivator = new \Magento\Update\Backup\UnixZipArchive($backupInfo);
        $result = $archivator->archive();
        $this->assertEquals($this->backupFileName, $result);

        // Change the contents of a.txt
        $this->autoRollbackHelper(1);
        $this->assertEquals('foo changed', file_get_contents($this->archivedDir . 'a.txt'));

        // Rollback process
        $this->rollBack->autoRollback();

        // Assert that the contents of a.txt has been restored properly
        $this->assertEquals('foo', file_get_contents($this->archivedDir . 'a.txt'));
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
            if (file_exists($this->archivedDir . $fileA)) {
                unlink($this->archivedDir . $fileA);
            }
            if (file_exists($this->archivedDir . $fileB)) {
                unlink($this->archivedDir . $fileB);
            }
            if (file_exists($this->archivedDir . $fileC)) {
                unlink($this->archivedDir . $fileC);
            }
        }
    }
}
