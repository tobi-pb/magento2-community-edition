<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Update;

/**
 * Class which provides access to the current status of the Magento updater application.
 *
 * Each job is using this class to share information about its current status.
 * Current status can be seen on the updater app web page.
 */
class Status
{
    /**
     * Path to a file, which content is displayed on the updater web page.
     *
     * @var string
     */
    protected $statusFilePath;

    /**
     * Path to a log file, which contains all the information displayed on the web page.
     *
     * Note that it can be cleared only manually, it is not cleared by clear() method.
     *
     * @var string
     */
    protected $logFilePath;

    /**
     * Initialize.
     *
     * @param string|null $statusFilePath
     * @param string|null $logFilePath
     */
    public function __construct($statusFilePath = null, $logFilePath = null)
    {
        $this->statusFilePath = $statusFilePath ? $statusFilePath : UPDATER_BP . '/var/.update_status.txt';
        $this->logFilePath = $logFilePath ? $logFilePath : UPDATER_BP . '/var/update_status.log';
    }

    /**
     * Get current updater application status.
     *
     * The last N status lines only may be requested using $maxNumberOfLines argument.
     *
     * To avoid display area overflow due to having overly long lines, $lineLengthLimit can be used.
     * E.g. if some line is 2.3 times longer than $lineLengthLimit, it will account for 3 lines.
     *
     * @param int|null $maxNumberOfLines
     * @param int $lineLengthLimit
     * @return string
     */
    public function get($maxNumberOfLines = null, $lineLengthLimit = 120)
    {
        $status = '';
        if (file_exists($this->statusFilePath)) {
            $fullStatusArray = file($this->statusFilePath);
            $linesInFile = count($fullStatusArray);
            if (!$maxNumberOfLines || ($maxNumberOfLines > $linesInFile)) {
                $maxNumberOfLines = $linesInFile;
            }
            $totalNumberOfLinesOnDisplay = 0;
            $totalLinesToRead = $maxNumberOfLines;
            for ($currentLineNumber = 1; $currentLineNumber <= $maxNumberOfLines; $currentLineNumber++) {
                $lineLength = strlen($fullStatusArray[$linesInFile - $currentLineNumber]);
                /** Line length is at least 1 because of new line character, so ceil should evaluate at least to 1 */
                $numberOfLinesOnDisplay = ceil($lineLength / $lineLengthLimit);
                $totalNumberOfLinesOnDisplay += $numberOfLinesOnDisplay;
                if ($numberOfLinesOnDisplay > 1) {
                    $totalLinesToRead -= $numberOfLinesOnDisplay - 1;
                    if ($totalLinesToRead < $currentLineNumber) {
                        $totalLinesToRead = $currentLineNumber;
                    }
                }
                if ($totalNumberOfLinesOnDisplay > $maxNumberOfLines) {
                    break;
                }
            }
            $slicedStatusArray = array_slice($fullStatusArray, -$totalLinesToRead, $totalLinesToRead);
            $status = implode('', $slicedStatusArray);
        }
        return $status;
    }

    /**
     * Add status update.
     *
     * Add information to a temporary file which is used for status display on a web page and to a permanent status log.
     *
     * @param string $text
     * @return $this
     * @throws \RuntimeException
     */
    public function add($text)
    {
        $currentUtcTime = '[' . date('Y-m-d H:i:s T', time()) . '] ';
        $text = $currentUtcTime . $text;
        $this->writeToFile($text, $this->logFilePath);
        $this->writeToFile($text, $this->statusFilePath);
        return $this;
    }

    /**
     * Write status information to the file.
     *
     * @param string $text
     * @param string $filePath
     * @return $this
     * @throws \RuntimeException
     */
    protected function writeToFile($text, $filePath)
    {
        $isNewFile = !file_exists($filePath);
        if (!$isNewFile && file_get_contents($filePath)) {
            $text = "\n{$text}";
        }
        if (false === file_put_contents($filePath, $text, FILE_APPEND)) {
            throw new \RuntimeException(sprintf('Cannot add status information to "%s"', $filePath));
        }
        if ($isNewFile) {
            chmod($filePath, 0777);
        }
        return $this;
    }

    /**
     * Clear current status.
     *
     * Note that this method does not clear status information from the permanent status log.
     *
     * @return $this
     * @throws \RuntimeException
     */
    public function clear()
    {
        if (!file_exists($this->statusFilePath)) {
            return $this;
        } else if (false === file_put_contents($this->statusFilePath, '')) {
            throw new \RuntimeException(sprintf('Cannot clear status information from "%s"', $this->statusFilePath));
        }
        return $this;
    }
}