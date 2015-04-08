<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Update\Queue\Composer;

/**
 * Class for managing Composer tool.
 */
class ComposerManager
{
    /** @var array */
    protected $composerContent;

    /** @var string */
    protected $composerFilePath;

    /** @var string */
    protected $composerFileName = 'composer.json';

    /**
     * Initialize dependencies.
     *
     * @param null $composerFilePath
     * @throws \Exception
     */
    public function __construct($composerFilePath = null)
    {
        $this->composerFilePath = $composerFilePath ? $composerFilePath : UPDATER_BP;
        $this->composerContent = null;
    }

    /**
     * Set data to the section of composer file
     *
     * Original values in the section will be replaced with value of the key in $data.
     * If value doesn't exist, it will be added.
     * Note if some key in $data is empty it will override original value with empty value
     *
     * @param $section
     * @param $data
     * @return $this
     */
    public function set($section, $data)
    {
        if (empty($data)) {
            return $this;
        }
        if (!isset($this->composerContent)) {
            $composerFile = $this->composerFilePath . DIRECTORY_SEPARATOR . $this->composerFileName;
            throw new \Exception(sprintf('Composer.json was not loaded into memory', $composerFile));
        }
        if (!isset($this->composerContent[$section])) {
            $this->composerContent[$section] = [];
        }
        $this->composerContent = array_replace_recursive(
            $this->composerContent,
            [$section => json_decode($data, true)]
        );
        return $this;
    }

    /**
     * Get required section from composer file in memory
     *
     * @param $section
     * @return bool|array
     */
    public function get($section)
    {
        if (!isset($this->composerContent[$section])) {
            return false;
        }
        return $this->composerContent[$section];
    }

    /**
     * Save composer content to file
     *
     * @return $this
     * @throws \Exception
     */
    public function save()
    {
        $this->writeComposerFile();
        return $this;
    }

    /**
     * Load composer content from file
     *
     * @return $this
     * @throws \Exception
     */
    public function load()
    {
        $this->composerContent = $this->readComposerFile();
        return $this;
    }

    /**
     * Run "composer update"
     *
     * @return bool
     * @throws \Exception
     */
    public function runUpdate()
    {
        $command = sprintf('cd %s && composer update', $this->composerFilePath);
        exec($command, $output, $return);
        if ($return) {
            throw new \Exception(sprintf('Command "composer update" failed: %s', join(' ', $output)));
        }
        return true;
    }

    /**
     * Read composer file into memory
     *
     * @return array|mixed
     * @throws \Exception
     */
    protected function readComposerFile()
    {
        $composerFile = $this->composerFilePath . DIRECTORY_SEPARATOR . $this->composerFileName;
        if (!file_exists($composerFile)) {
            throw new \Exception(sprintf('Composer file "%s" doesn\'t exists', $composerFile));
        }
        $content = file_get_contents($composerFile);
        if ($content === false) {
            throw new \Exception(sprintf('Could not read from composer file "%s"', $composerFile));
        }
        $this->composerContent = json_decode($content, true);
        return $this->composerContent;
    }

    /**
     * Write composer file from memory
     *
     * @throws \Exception
     */
    protected function writeComposerFile()
    {
        $composerFile = $this->composerFilePath . DIRECTORY_SEPARATOR . $this->composerFileName;
        if (!isset($this->composerContent)) {
            throw new \Exception(sprintf('Composer.json was not loaded into memory', $composerFile));
        }
        $result = file_put_contents(
            $composerFile,
            json_encode($this->composerContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL
        );
        if ($result === false) {
            throw new \Exception(sprintf('Could not write to composer file "%s"', $composerFile));
        }
    }
}
