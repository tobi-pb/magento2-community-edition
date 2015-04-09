<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Update\Queue\JobUpdate;

class ComposerManagerTest extends \PHPUnit_Framework_TestCase
{
    /** @var string */
    protected $composerConfigFileDir;

    /** @var string */
    protected $expectedRequireDirectiveParam;

    /** @var string */
    protected $composerContent;

    protected function setUp()
    {
        parent::setUp();
        $this->composerConfigFileDir = realpath(__DIR__ . '/../../_files');
        $this->expectedRequireDirectiveParam = [
            ["name" => "php", "version" => "~5.6.0"],
            ["name" => "composer/composer", "version" => "1.0.0-alpha8"]
        ];
        $this->composerContent = file_get_contents($this->composerConfigFileDir . '/composer.json');
    }

    protected function tearDown()
    {
        parent::tearDown();
        file_put_contents($this->composerConfigFileDir . '/composer.json', $this->composerContent);
    }

    public function testUpdateComposerConfigFile()
    {
        $composerManager = new ComposerManager($this->composerConfigFileDir);
        $composerManager->updateComposerConfigFile('require', $this->expectedRequireDirectiveParam);
        $expectedRequireDirective = ["php" => "~5.6.0", "composer/composer" => "1.0.0-alpha8"];
        $actualRequireDirective = json_decode(
            file_get_contents($this->composerConfigFileDir . '/composer.json'),
            true
        )['require'];

        $this->assertEquals($expectedRequireDirective, $actualRequireDirective );
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage  Application does not support composer's directive "nonSupport"
     */
    public function testUpdateComposerConfigFileNonSupportedDirective()
    {
        $composerManager = new ComposerManager($this->composerConfigFileDir);
        $composerManager->updateComposerConfigFile('nonSupport', $this->expectedRequireDirectiveParam);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage  Incorrect/missed parameters for composer's directive "require"
     */
    public function testUpdateComposerConfigFileMissedParam()
    {
        $expectedRequireDirectiveParam = [
            ["name" => "php"],
        ];
        $composerManager = new ComposerManager($this->composerConfigFileDir);
        $composerManager->updateComposerConfigFile('require', $expectedRequireDirectiveParam);
    }
}
