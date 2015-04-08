<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Update\Queue\Composer;

class ComposerManagerTest extends \PHPUnit_Framework_TestCase
{
    /** @var string */
    protected $composerFilePath;

    /** @var string */
    protected $expectedJsonRequireSection;

    /** @var string */
    protected $composerContent;

    protected function setUp()
    {
        parent::setUp();
        $this->composerFilePath = realpath(__DIR__ . '/../../_files');
        $this->expectedJsonRequireSection = '{"php": "~5.6.0", "composer/composer": "1.0.0-alpha8"}';
        $this->composerContent = file_get_contents($this->composerFilePath . DIRECTORY_SEPARATOR . 'composer.json');
    }

    protected function tearDown()
    {
        parent::tearDown();
        file_put_contents($this->composerFilePath . DIRECTORY_SEPARATOR . 'composer.json', $this->composerContent);
    }

    public function testSet()
    {
        $composerManager = new ComposerManager($this->composerFilePath);
        $actualRequireSection = $composerManager->load()
            ->set('require', $this->expectedJsonRequireSection)
            ->get('require');
        $this->assertEquals($actualRequireSection, json_decode($this->expectedJsonRequireSection, true));
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage  Composer.json was not loaded into memory
     */
    public function testSetWithoutLoadFile()
    {
        $composerManager = new ComposerManager($this->composerFilePath);
        $composerManager->set('require', $this->expectedJsonRequireSection);
    }

    public function testGetNonExistentSection()
    {
        $composerManager = new ComposerManager($this->composerFilePath);
        $this->assertFalse($composerManager->load()->get('require-non-existent'));
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage  Composer file "nonExistentPath\composer.json" doesn't exists
     */
    public function testLoadNonExistentFile()
    {
        $composerManager = new ComposerManager('nonExistentPath');
        $composerManager->load();
        $this->assertEquals($composerManager->get('require'), json_decode($this->expectedJsonRequireSection, true));
    }

    public function testSave()
    {
        $composerManager = new ComposerManager($this->composerFilePath);
        $composerManager->load()
            ->set('require', $this->expectedJsonRequireSection)
            ->save();
        unset($composerManager);
        $composerManager = new ComposerManager($this->composerFilePath);
        $actualRequireSection = $composerManager->load()->get('require');
        $this->assertEquals($actualRequireSection, json_decode($this->expectedJsonRequireSection, true));
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage  Composer.json was not loaded into memory
     */
    public function testSaveWithoutLoadFile()
    {
        $composerManager = new ComposerManager($this->composerFilePath);
        $composerManager->save();
    }
}
