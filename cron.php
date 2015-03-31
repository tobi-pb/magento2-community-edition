<?php
/**
 * Create/Update .update_status.txt with current date and time.
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

$updateStatusFile = fopen(__DIR__ . '/var/.update_status.txt', 'w');
date_default_timezone_set('UTC');
$currentDate = date('m/d/Y h:i:s a', time());
fwrite($updateStatusFile, $currentDate);
fclose($updateStatusFile);
