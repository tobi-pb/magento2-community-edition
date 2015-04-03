<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Update;

class StatusTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Update\Status
     */
    protected $status;

    /**
     * @var string
     */
    protected $statusFilePath;

    /**
     * @var string
     */
    protected $tmpStatusFilePath;

    protected function setUp()
    {
        parent::setUp();
        $this->statusFilePath = __DIR__ . '/_files/update_status.txt';
        $this->tmpStatusFilePath = TESTS_TEMP_DIR . '/update_status.txt';

        /** Prepare temporary status file which can be modified */
        $statusFileContent = file_get_contents($this->statusFilePath);
        file_put_contents($this->tmpStatusFilePath, $statusFileContent);
        /** Make sure it was created */
        $this->assertEquals($statusFileContent, file_get_contents($this->tmpStatusFilePath), "Precondition failed.");
    }

    protected function tearDown()
    {
        parent::tearDown();
        if (file_exists($this->tmpStatusFilePath)) {
            unlink($this->tmpStatusFilePath);
        }
    }

    /**
     * @dataProvider getProvider
     * @param int $maximumNumberOfLines
     * @param int $lineLengthLimit
     * @param int $expectedNumberOfReadLines
     */
    public function testGet($maximumNumberOfLines, $lineLengthLimit, $expectedNumberOfReadLines)
    {
        $status = new \Magento\Update\Status($this->statusFilePath);
        if ($maximumNumberOfLines) {
            if ($lineLengthLimit) {
                $actualStatusText = $status->get($maximumNumberOfLines, $lineLengthLimit);
            } else {
                $actualStatusText = $status->get($maximumNumberOfLines);
            }
        } else {
            $actualStatusText = $status->get();
        }
        /** Ensure that number of read lines is correct */
        $actualNumberOfLinesRead = count(explode("\n", $actualStatusText));
        $this->assertEquals(
            $expectedNumberOfReadLines,
            $actualNumberOfLinesRead,
            'Number of actually read lines is incorrect.'
        );

        /** Ensure that actual status text is correct */
        $statusArray = file($this->statusFilePath);
        $expectedStatusText = implode(
            '',
            array_slice($statusArray, -$expectedNumberOfReadLines, $expectedNumberOfReadLines)
        );
        $this->assertEquals($expectedStatusText, $actualStatusText);
    }

    public function getProvider()
    {
        return [
            'Full content, no arguments' => [
                'maximumNumberOfLines' => null,
                'lineLengthLimit' => null,
                'expectedNumberOfReadLines' => 15
            ],
            'Full content, with first argument' => [
                'maximumNumberOfLines' => 100,
                'lineLengthLimit' => null,
                'expectedNumberOfReadLines' => 15
            ],
            'Full content, with arguments' => [
                'maximumNumberOfLines' => 100,
                'lineLengthLimit' => 500,
                'expectedNumberOfReadLines' => 17
            ],
            '5 long lines' => [
                'maximumNumberOfLines' => 5,
                'lineLengthLimit' => 500,
                'expectedNumberOfReadLines' => 5
            ],
            '10 short lines (100 chars per line)' => [
                'maximumNumberOfLines' => 10,
                'lineLengthLimit' => 100,
                'expectedNumberOfReadLines' => 6
            ],
            '10 short lines (30 chars per line)' => [
                'maximumNumberOfLines' => 10,
                'lineLengthLimit' => 30,
                'expectedNumberOfReadLines' => 2
            ],
        ];
    }
}