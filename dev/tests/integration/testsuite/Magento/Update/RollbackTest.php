<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Update;

class RollbackTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Exception
     * @expectedExceptionMessage No available backup file found.
     */
    public function testAutoRollbackBackupFileUnavailable()
    {
        $rollBack = new \Magento\Update\Rollback();
        $rollBack->autoRollback();
    }
}
