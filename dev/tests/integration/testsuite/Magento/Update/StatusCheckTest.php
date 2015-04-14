<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Update;

class StatusCheckTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $indexScript;

    /**
     * @var \Magento\Update\Status
     */
    protected $status;

    protected function setUp()
    {
        $this->indexScript = UPDATER_BP . '/index.php';
        $this->status = new \Magento\Update\Status();
        $this->status->clear();
    }

    protected function tearDown()
    {
        $this->status->clear();
    }

    /**
     * @param bool $isInProgress
     * @param string $statusMessage
     * @dataProvider progressStatusDataProvider
     */
    public function testStatusCheck($isInProgress, $statusMessage)
    {
        $uniqueMessage = 'Test Message' . uniqid();
        $this->status->add($uniqueMessage);
        $this->status->setUpdateInProgress($isInProgress);
        $actualResponse = shell_exec('php -f ' . $this->indexScript);
        $this->assertContains($uniqueMessage, $actualResponse);
        $this->assertContains($statusMessage, $actualResponse);
    }

    /**
     * Return status info
     *
     * @return array
     */
    public function progressStatusDataProvider()
    {
        return [
            'isRunning' => [
                'isInProgress' => true,
                'statusMessage' => 'Update application is running'
            ],
            'isNotRunning' => [
                'isInProgress' => false,
                'statusMessage' => 'Update application is NOT running'
            ],
        ];
    }
}
