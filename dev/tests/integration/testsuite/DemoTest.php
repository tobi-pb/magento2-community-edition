<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * TODO: Remove demo test when any real test is available
 */
class DemoTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Temporary test to ensure that testing framework works.
     */
    public function testFramework()
    {
        $demoObject = new Magento\DemoClass();
        $this->assertNotEmpty($demoObject->getCurrentDateTime());
    }
}
