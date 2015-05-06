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

    /**
     * @var string
     */
    protected $tmpStatusLogFilePath;

    /**
     * @var string
     */
    protected $updateInProgressFlagFilePath;

    /**
     * @var string
     */
    protected $updateErrorFlagFilePath;

    protected function setUp()
    {
        parent::setUp();
        $this->statusFilePath = __DIR__ . '/_files/update_status.txt';
        $this->tmpStatusFilePath = TESTS_TEMP_DIR . '/update_status.txt';
        $this->tmpStatusLogFilePath = TESTS_TEMP_DIR . '/update_status.log';
        $this->updateInProgressFlagFilePath = TESTS_TEMP_DIR . '/update_in_progress.flag';
        $this->updateErrorFlagFilePath = TESTS_TEMP_DIR . '/update_error.flag';

        $statusFileContent = file_get_contents($this->statusFilePath);
        /** Prepare temporary status file which can be modified */
        file_put_contents($this->tmpStatusFilePath, $statusFileContent);
        /** Make sure it was created */
        $this->assertEquals($statusFileContent, file_get_contents($this->tmpStatusFilePath), "Precondition failed.");

        file_put_contents($this->tmpStatusLogFilePath, $statusFileContent);
        $this->assertEquals($statusFileContent, file_get_contents($this->tmpStatusLogFilePath), "Precondition failed.");
    }

    protected function tearDown()
    {
        parent::tearDown();
        if (file_exists($this->tmpStatusFilePath)) {
            unlink($this->tmpStatusFilePath);
        }
        if (file_exists($this->tmpStatusLogFilePath)) {
            unlink($this->tmpStatusLogFilePath);
        }
        if (file_exists($this->updateInProgressFlagFilePath)) {
            unlink($this->updateInProgressFlagFilePath);
        }
        if (file_exists($this->updateErrorFlagFilePath)) {
            unlink($this->updateErrorFlagFilePath);
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

    public function testGetFileDoesNotExixt()
    {
        $status = new \Magento\Update\Status('invalid_file_path');
        $this->assertEquals('', $status->get());
    }

    public function testGetEmptyFile()
    {
        file_put_contents($this->tmpStatusFilePath, '');
        $status = new \Magento\Update\Status($this->tmpStatusFilePath);
        $this->assertEquals('', $status->get());
    }

    public function testAdd()
    {
        $originalStatus = file_get_contents($this->tmpStatusFilePath);
        $status = new \Magento\Update\Status($this->tmpStatusFilePath, $this->tmpStatusLogFilePath);

        $firstUpdate = <<<FIRST_UPDATE
Praesent blandit dolor.
Sed non quam.
FIRST_UPDATE;
        $status->add($firstUpdate);
        $textAfterFirstUpdate = "$originalStatus\n{$firstUpdate}";
        $this->verifyAddedStatus($textAfterFirstUpdate, $this->tmpStatusFilePath, 1);
        $this->verifyAddedStatus($textAfterFirstUpdate, $this->tmpStatusLogFilePath, 1);

        $secondUpdate = <<<SECOND_UPDATE
Donec lacus nunc, viverra nec, blandit vel, egestas et, augue.

Vestibulum tincidunt malesuada tellus. Ut ultrices ultrices enim.
Curabitur sit amet mauris. Morbi in dui quis est pulvinar ullamcorper.
SECOND_UPDATE;
        $this->assertInstanceOf('Magento\Update\Status', $status->add($secondUpdate));
        $textAfterSecondUpdate = "{$originalStatus}\n{$firstUpdate}\n{$secondUpdate}";
        $this->verifyAddedStatus($textAfterSecondUpdate, $this->tmpStatusFilePath, 2);
        $this->verifyAddedStatus($textAfterSecondUpdate, $this->tmpStatusLogFilePath, 2);
    }

    public function testAddToNotExistingFile()
    {
        unlink($this->tmpStatusFilePath);
        $this->assertFalse(file_exists($this->tmpStatusFilePath), "Precondition failed.");

        $status = new \Magento\Update\Status($this->tmpStatusFilePath);
        $statusUpdate = <<<STATUS_UPDATE
Praesent blandit dolor.
Sed non quam.
STATUS_UPDATE;
        $status->add($statusUpdate, $this->tmpStatusFilePath);
        $this->verifyAddedStatus($statusUpdate, $this->tmpStatusFilePath, 1);
    }

    public function testClear()
    {
        $originalLogFileContent = file_get_contents($this->tmpStatusLogFilePath);
        $status = new \Magento\Update\Status($this->tmpStatusFilePath, $this->tmpStatusLogFilePath);
        $this->assertInstanceOf('Magento\Update\Status', $status->clear());
        $this->assertEquals('', file_get_contents($this->tmpStatusFilePath));
        $this->assertEquals($originalLogFileContent, file_get_contents($this->tmpStatusLogFilePath));
    }

    public function testClearNotExistingFile()
    {
        unlink($this->tmpStatusFilePath);
        $this->assertFalse(file_exists($this->tmpStatusFilePath), "Precondition failed.");

        $status = new \Magento\Update\Status($this->tmpStatusFilePath);
        $this->assertInstanceOf('Magento\Update\Status', $status->clear());
        $this->assertFalse(file_exists($this->tmpStatusFilePath));
    }

    public function testIsUpdateInProgress()
    {
        $status = new \Magento\Update\Status(
            $this->tmpStatusFilePath,
            $this->tmpStatusLogFilePath,
            $this->updateInProgressFlagFilePath
        );
        $this->assertFalse($status->isUpdateInProgress());

        $this->assertInstanceOf('Magento\Update\Status', $status->setUpdateInProgress());
        $this->assertTrue($status->isUpdateInProgress());

        $this->assertInstanceOf('Magento\Update\Status', $status->setUpdateInProgress(false));
        $this->assertFalse($status->isUpdateInProgress());

        $this->assertInstanceOf('Magento\Update\Status', $status->setUpdateInProgress(true));
        $this->assertTrue($status->isUpdateInProgress());
    }

    public function testIsUpdateError()
    {
        $status = new \Magento\Update\Status(
            $this->tmpStatusFilePath,
            $this->tmpStatusLogFilePath,
            $this->updateInProgressFlagFilePath,
            $this->updateErrorFlagFilePath
        );
        $this->assertFalse($status->isUpdateError());

        $this->assertInstanceOf('Magento\Update\Status', $status->setUpdateError());
        $this->assertTrue($status->isUpdateError());

        $this->assertInstanceOf('Magento\Update\Status', $status->setUpdateError(false));
        $this->assertFalse($status->isUpdateError());

        $this->assertInstanceOf('Magento\Update\Status', $status->setUpdateError(true));
        $this->assertTrue($status->isUpdateError());
    }

    /**
     * @param string $expectedTextAfterUpdate
     * @param string $filePath
     * @param int $expectedNumberOfTimeEntries
     */
    protected function verifyAddedStatus($expectedTextAfterUpdate, $filePath, $expectedNumberOfTimeEntries)
    {
        $actualStatusText = file_get_contents($filePath);
        /** Make sure that number of current date/time entries matches expected value */
        preg_match_all('/\[.*?\]\s/', $actualStatusText, $matches);
        $this->assertCount(1, $matches);
        $this->assertCount($expectedNumberOfTimeEntries, $matches[0]);

        /** Eliminate current date/time entries from the actual status content before text comparison */
        $actualStatusText = preg_replace('/\[.*?\]\s/', '', $actualStatusText);
        $this->assertEquals($expectedTextAfterUpdate, $actualStatusText);
    }
}