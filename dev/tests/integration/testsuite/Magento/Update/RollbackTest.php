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

    protected function setup()
    {
        $this->rollBack = new \Magento\Update\Rollback(
            UPDATER_BP . '/dev/tests/integration/testsuite/Magento/Update/_files/backup'
        );
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage The backup file specified by update_queue.json does not exist.
     */
    public function testManualRollbackBackupFileUnavailable()
    {
        $this->rollBack->manualRollback('');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage No available backup file found.
     */
    public function testAutoRollbackBackupFileUnavailable()
    {
        $this->rollBack->autoRollback();
    }
}
