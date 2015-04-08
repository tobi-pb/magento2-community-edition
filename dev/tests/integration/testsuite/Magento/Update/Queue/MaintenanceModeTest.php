<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Update\Queue;

class MaintenanceModeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Path to the maintenance flag file
     * @var string
     */
    protected $flagFile;

    /**
     * Path to the IP addresses file
     * @var string
     */
    protected $ipFile;

    /**
     * @var MaintenanceMode
     */
    protected $maintenanceMode;

    public function __construct() {
        $this->flagFile = TESTS_TEMP_DIR . '/.maintenance.flag';
        $this->ipFile = TESTS_TEMP_DIR . '/.maintenance.ip';
    }

    protected function setUp()
    {
        $this->maintenanceMode = new \Magento\Update\Queue\MaintenanceMode($this->flagFile, $this->ipFile);
    }

    protected function tearDown()
    {
        if (file_exists($this->flagFile)) {
            unlink($this->flagFile);
        }
        if (file_exists($this->ipFile)) {
            unlink($this->ipFile);
        }
    }
    public function testSetOnIsOn()
    {
        $this->maintenanceMode->set(true);
        $this->assertTrue($this->maintenanceMode->isOn());
        $this->assertTrue(file_exists($this->flagFile));
    }

    public function testSetOffIsOn()
    {
        $this->maintenanceMode->set(false);
        $this->assertFalse($this->maintenanceMode->isOn());
        $this->assertFalse(file_exists($this->flagFile));
        // test turning off when already off
        $this->maintenanceMode->set(false);
        $this->assertFalse($this->maintenanceMode->isOn());
    }

    public function testSetAddresses()
    {
        $this->maintenanceMode->setAddresses('123.123.123.123,234.234.234.234');
        $this->assertTrue(file_exists($this->ipFile));
        $this->assertTrue($this->maintenanceMode->set(true));
        $this->assertFalse($this->maintenanceMode->isOn('123.123.123.123'));
        $this->assertFalse($this->maintenanceMode->isOn('234.234.234.234'));
    }

    public function testSetAddressesEmpty()
    {
        file_put_contents($this->ipFile, '123.123.123.123,234.234.234.234');
        $this->maintenanceMode->setAddresses('');
        $this->assertFalse(file_exists($this->ipFile));
        $this->maintenanceMode->set(true);
        $this->assertTrue($this->maintenanceMode->isOn('123.123.123.123'));
        // test setting empty after already removing IP file
        $this->maintenanceMode->setAddresses('');
        $this->assertFalse(file_exists($this->ipFile));
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedMessage One or more IP-addresses is expected (comma-separated)
     */
    public function testSetAddressesBadData()
    {
        $this->maintenanceMode->setAddresses('jdf k;dsk.123.123.123,234.234.234.234');
    }

    public function testGetAddressInfo()
    {
        $ipAddresses = ['123.123.123.123','234.234.234.234'];
        $this->assertTrue($this->maintenanceMode->setAddresses(implode(',', $ipAddresses)));
        $actualAddresses = $this->maintenanceMode->getAddressInfo();
        $this->assertEquals($ipAddresses, $actualAddresses);
    }
}
