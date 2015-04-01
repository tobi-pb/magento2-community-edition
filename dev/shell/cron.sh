#!/bin/sh
# Copyright © 2015 Magento. All rights reserved.
# See COPYING.txt for license details.

CRONSCRIPT="../../cron.php"
PHP_BIN=`which php`
$PHP_BIN $CRONSCRIPT &
