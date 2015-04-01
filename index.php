<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

require_once __DIR__ . '/vendor/autoload.php';

$filename = 'var/.update_status.txt';
$headerText = 'Last Update';
$messageText = '';

if (file_exists($filename))
{
    try {
        $updateStatusFile = fopen($filename, 'r');
        $messageText = fread($updateStatusFile, filesize($filename));
        fclose($updateStatusFile);
    } catch (\Exception $e) {
        $headerText = 'Error';
        $messageText = 'There was an error opening the file';
    }
} else {
    $headerText = 'File does not exist';
    $messageText = 'Please run the cron script';
}

echo <<<HTML
<div style="font:12px/1.35em arial, helvetica, sans-serif;">
    <div style="margin:0 0 25px 0; border-bottom:1px solid #ccc;">
        <h3 style="margin:0;font-size:1.7em;font-weight:normal;text-transform:none;text-align:left;color:#2f2f2f;">
        {$headerText}</h3>
    </div>
    <p>{$messageText}</p>
</div>
HTML;

