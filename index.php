<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

require_once __DIR__ . '/vendor/autoload.php';

$headerText = 'Last Update';
$messageText = file_get_contents('var/.update_status.txt');
if (!$messageText) {
    $headerText = 'Error';
    $messageText = 'There was an error opening the file';
}

echo <<<HTML
<link rel="stylesheet" href="pub/style.css">
    <div style="margin:0 0 25px 0; border-bottom:1px solid #ccc;">
        <h3>
        {$headerText}</h3>
    </div>
    <p>{$messageText}</p>
HTML;

