<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

error_reporting(E_ALL);

define('UPDATER_BP', realpath(__DIR__ . '/../'));
define('UPDATER_BACKUP_DIR', UPDATER_BP . '/var/backup/');
define('MAGENTO_BP', realpath(__DIR__ . '/../../'));

date_default_timezone_set('UTC');

require_once UPDATER_BP . '/vendor/autoload.php';
