<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Update;

class QueueTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Update\Queue
     */
    protected $queue;

    /**
     * @var \Magento\Update\Queue\Reader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $queueReaderMock;

    /**
     * @var \Magento\Update\Queue\JobFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $jobFactoryMock;

    protected function setUp()
    {
        parent::setUp();
        $this->queueReaderMock = $this->getMockBuilder('Magento\Update\Queue\Reader')
            ->disableOriginalConstructor()
            ->getMock();
        $this->jobFactoryMock = $this->getMockBuilder('Magento\Update\Queue\JobFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->queue = new \Magento\Update\Queue($this->queueReaderMock, $this->jobFactoryMock);
    }

    public function testPopQueueJob()
    {
        $queueJson = file_get_contents(__DIR__ . '/_files/update_queue_valid.json');
        $this->queueReaderMock->expects($this->once())->method('read')->willReturn($queueJson);
        $jobMock = $this->getMockBuilder('Magento\Update\Queue\AbstractJob')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->jobFactoryMock->expects($this->exactly(4))->method('create')->willReturn($jobMock);
        /** Ensure that arguments are passed correctly to the job factory */
        $this->jobFactoryMock->expects($this->at(0))->method('create')->with('backup', []);
        $this->queueReaderMock->expects($this->once())->method('clearQueue');
        $jobs = $this->queue->popQueuedJobs();
        $this->assertCount(4, $jobs);
        $this->assertInternalType('array', $jobs);
        $this->assertSame($jobMock, $jobs[3]);
    }

    public function testPopQueueJobEmptyQueueFile()
    {
        $queueJson = '';
        $this->queueReaderMock->expects($this->once())->method('read')->willReturn($queueJson);
        $this->assertEquals([], $this->queue->popQueuedJobs());
    }

    /**
     * @dataProvider popQueueJobInvalidQueueFormatProvider
     * @param string $queueJson
     * @param string $expectedExceptionMessage
     */
    public function testPopQueueJobInvalidQueueFormat($queueJson, $expectedExceptionMessage)
    {
        $this->setExpectedException('\RuntimeException', $expectedExceptionMessage);
        $this->queueReaderMock->expects($this->once())->method('read')->willReturn($queueJson);
        $this->queue->popQueuedJobs();

    }

    public function popQueueJobInvalidQueueFormatProvider()
    {
        return [
            'Missing "jobs" field' => [
                '{"invalid": []}',
                '"jobs" field is missing or is not an array.'

            ],
            'Incorrect format of "jobs" field' => [
                '{"jobs": "string_value"}',
                '"jobs" field is missing or is not an array.'

            ],
            'Missing job name' => [
                '{"jobs": [{"params": {}}]}',
                '"name" field is missing for one or more jobs.'

            ],
            'Missing job params' => [
                '{"jobs": [{"name": "backup"}]}',
                '"params" field is missing for one or more jobs.'
            ],
        ];
    }
}