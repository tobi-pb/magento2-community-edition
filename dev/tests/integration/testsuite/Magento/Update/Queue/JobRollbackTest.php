<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Update\Queue;

class JobRollbackTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Missing required parameter: backup_file_name
     */
    public function testManualRollbackNoParameter()
    {
        $jobRollback = new \Magento\Update\Queue\JobRollback(
            'rollback',
            ['backup_file_name' => null]
        );
        $jobRollback->execute();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage The backup file specified by update_queue.json does not exist.
     */
    public function testManualRollbackBackupFileUnavailable()
    {
        $backupFileName = UPDATER_BP . '/dev/tests/integration/testsuite/Magento/Update/_files/backup/' . 'fake.zip';
        $jobRollback = new \Magento\Update\Queue\JobRollback(
            'rollback',
            ['backup_file_name' => $backupFileName]
        );
        $jobRollback->execute();
    }
}
