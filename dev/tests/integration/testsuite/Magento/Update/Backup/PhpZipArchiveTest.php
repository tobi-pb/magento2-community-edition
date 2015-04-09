<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Update\Backup;

class PhpArchiveTest extends \PHPUnit_Framework_TestCase
{
    /** @var  string */
    protected $backupFilename;

    /** @var  string */
    protected $backupPath;

    protected function setUp()
    {
        parent::setUp();
        $this->backupFilename = uniqid('test_backup') . '.zip';
        $this->backupPath = UPDATER_BP . '/var/backup/';
    }

    protected function tearDown()
    {
        parent::tearDown();
        if (file_exists($this->backupPath . $this->backupFilename)) {
            unlink($this->backupPath . $this->backupFilename);
        }
    }

    public function testArchive()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            $this->markTestSkipped();
        }
        $backupInfo = $this->getMockBuilder('Magento\Update\Backup\BackupInfo')
            ->disableOriginalConstructor()
            ->setMethods(['getBackupFilename', 'getBlacklist', 'getArchivedDirectory'])
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

        $archivator = new \Magento\Update\Backup\PhpZipArchive($backupInfo);
        $result = $archivator->archive();
        $this->assertEquals($this->backupFilename, $result);
        $fileContent = file_get_contents($this->backupPath . $this->backupFilename);
        $this->assertNotEmpty($fileContent);
    }
}
