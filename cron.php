<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

require_once __DIR__ . '/app/bootstrap.php';

$updateStatusFile = fopen(__DIR__ . '/var/.update_status.txt', 'w');
$demoObject = new Magento\DemoClass();
fwrite($updateStatusFile, $demoObject->getCurrentDateTime());
fclose($updateStatusFile);
