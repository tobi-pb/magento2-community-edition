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
    protected $composerConfigFilePath;

    /** @var string */
    protected $expectedRequireDirectiveParam;

    /** @var string */
    protected $composerContent;

    protected function setUp()
    {
        parent::setUp();
        $this->composerConfigFileDir = TESTS_TEMP_DIR;
        $this->composerConfigFilePath = $this->composerConfigFileDir . '/composer.json';
        copy(__DIR__ . '/../../_files/composer.json', $this->composerConfigFilePath);
        $this->expectedRequireDirectiveParam = [
            [ComposerManager::PACKAGE_NAME => "php", ComposerManager::PACKAGE_VERSION => "~5.6.0"],
            [ComposerManager::PACKAGE_NAME => "composer/composer", ComposerManager::PACKAGE_VERSION => "1.0.0-alpha8"]
        ];
    }

    protected function tearDown()
    {
        parent::tearDown();
        unlink($this->composerConfigFilePath);
    }

    public function testUpdateComposerConfigFile()
    {
        $composerManager = new ComposerManager($this->composerConfigFileDir);
        $composerManager->updateComposerConfigFile('require', $this->expectedRequireDirectiveParam);
        $expectedRequireDirective = ["php" => "~5.6.0", "composer/composer" => "1.0.0-alpha8"];
        $actualRequireDirective = json_decode(file_get_contents($this->composerConfigFilePath), true)['require'];
        $this->assertEquals($expectedRequireDirective, $actualRequireDirective );
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Composer directive "nonSupport" is not supported
     */
    public function testUpdateComposerConfigFileNonSupportedDirective()
    {
        $composerManager = new ComposerManager($this->composerConfigFileDir);
        $composerManager->updateComposerConfigFile('nonSupport', $this->expectedRequireDirectiveParam);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Incorrect/missing parameters for composer directive "require"
     */
    public function testUpdateComposerConfigFileMissedParam()
    {
        $expectedRequireDirectiveParam = [
            [ComposerManager::PACKAGE_NAME => "php"],
        ];
        $composerManager = new ComposerManager($this->composerConfigFileDir);
        $composerManager->updateComposerConfigFile('require', $expectedRequireDirectiveParam);
    }
}
