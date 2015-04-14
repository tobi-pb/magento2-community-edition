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
     * Composer command
     */
    const COMPOSER_UPDATE = 'update';
    const COMPOSER_REQUIRE = 'require';
    /**#@-*/

    const PACKAGE_NAME = 'package_name';
    const PACKAGE_VERSION = 'package_version';

    /** @var string */
    protected $composerConfigFileDir;

    /**
     * Initialize dependencies.
     *
     * @param string|null $composerConfigFileDir
     */
    public function __construct($composerConfigFileDir = null)
    {
        $this->composerConfigFileDir = $composerConfigFileDir ? $composerConfigFileDir : MAGENTO_BP;
    }

    /**
     * Update composer config file using provided directive
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
            throw new \LogicException(sprintf('Composer directive "%s" is not supported', $directive));
        }
        return call_user_func([$this, $directiveHandler], $params);
    }

    /**
     * Run "composer update"
     *
     * @return bool
     * @throws \RuntimeException
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
        $packageList = [];
        foreach ($params as $param) {
            if (!isset($param[self::PACKAGE_NAME]) || !isset($param[self::PACKAGE_VERSION])) {
                throw new \RuntimeException('Incorrect/missing parameters for composer directive "require"');
            }
            $commandParams .= $param[self::PACKAGE_NAME] . ':' . $param[self::PACKAGE_VERSION] . ' ';
            $packageList[] = $param[self::PACKAGE_NAME];
        }
        $this->removeReplaceDirective($packageList);
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
        $fullCommand = sprintf(
            'cd %s && php -f %s/vendor/composer/composer/bin/composer %s %s',
            $this->composerConfigFileDir,
            UPDATER_BP,
            $command,
            $commandParams
        );

        exec($fullCommand, $output, $return);

        if ($return) {
            throw new \RuntimeException(sprintf('Command "%s" failed: %s', $fullCommand, join("\n", $output)));
        }
        return true;
    }

    /**
     * Remove replace directive in composer config file
     *
     * @param string[] $packageList
     * @return void
     */
    protected function removeReplaceDirective($packageList)
    {
        if (!empty($packageList)) {
            $composerFilePath = $this->composerConfigFileDir . '/composer.json';
            $fileContent = file_get_contents($composerFilePath);
            $fileJsonFormat = json_decode($fileContent, true);
            $key = 'replace';
            if (array_key_exists($key, $fileJsonFormat)) {
                foreach ($packageList as $packageName) {
                    if (array_key_exists($packageName, $fileJsonFormat[$key])) {
                        unset($fileJsonFormat[$key][$packageName]);
                    }
                }
                $newFileContent = json_encode($fileJsonFormat, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                file_put_contents($composerFilePath, $newFileContent . "\n");
            }
        }
    }
}
