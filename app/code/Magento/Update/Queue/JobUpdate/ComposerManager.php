<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Update\Queue\JobUpdate;

/**
 * Class for managing main Magento composer configuration file.
 */
class ComposerManager
{
    /**#@+
     * Composer commands
     */
    const COMPOSER_UPDATE = 'update';
    const COMPOSER_REQUIRE = 'require';
    /**#@-*/

    /** @var string */
    protected $composerConfigFileDir;

    /**
     * Initialize dependencies.
     *
     * @param string|null $composerConfigFileDir
     */
    public function __construct($composerConfigFileDir = null)
    {
        // TODO: replace UPDATER_BP with MAGENTO_BP
        $this->composerConfigFileDir = $composerConfigFileDir ? $composerConfigFileDir : UPDATER_BP;
    }

    /**
     * Update the data of the directive in composer config file
     *
     * @param string $directive
     * @param array $params
     * @return bool
     */
    public function updateComposerConfigFile($directive, array $params)
    {
        $camelCaseDirective = '';
        foreach (explode('-', $directive) as $item) {
            $camelCaseDirective .= ucfirst($item);
        }
        $directiveHandler = sprintf('update%sDirective', $camelCaseDirective);
        if (!method_exists($this, $directiveHandler)) {
            throw new \RuntimeException(sprintf('Application does not support composer\'s directive "%s"', $directive));
        }
        return call_user_func([$this, $directiveHandler], $params);
    }

    /**
     * Run "composer update"
     *
     * @return bool
     * @throws \Exception
     */
    public function runUpdate()
    {
        return $this->runComposerCommand(self::COMPOSER_UPDATE);
    }

    /**
     * Update require directive in composer config file
     *
     * @param array $params
     * @return bool
     * @throws \RuntimeException
     */
    protected function updateRequireDirective(array $params)
    {
        $commandParams = '';
        foreach ($params as $param) {
            if (!isset($param['name']) || !isset($param['version'])) {
                throw new \RuntimeException('Incorrect/missed parameters for composer\'s directive "require"');
            }
            $commandParams .= $param['name'] . ':' . $param['version'] . ' ';
        }
        $commandParams .= ' --no-update';
        return $this->runComposerCommand(self::COMPOSER_REQUIRE, $commandParams);
    }

    /**
     * Run composer command
     *
     * @param string $command
     * @param string|null $commandParams
     * @return bool
     * @throws \RuntimeException
     */
    protected function runComposerCommand($command, $commandParams = null)
    {
        $fullCommand = sprintf('cd %s && composer %s %s', $this->composerConfigFileDir, $command, $commandParams);

        exec($fullCommand, $output, $return);

        if ($return) {
            throw new \RuntimeException(sprintf('Command "composer %s" failed: %s', join(' ', $output), $command));
        }
        return true;
    }
}
