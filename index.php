<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

require_once __DIR__ . '/app/bootstrap.php';

$status = new \Magento\Update\Status();
if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    /* Ajax request processing */
    include __DIR__ . '/app/code/Magento/Update/view/templates/status/message.phtml';
} else {
    include __DIR__ . '/app/code/Magento/Update/view/templates/status.phtml';
}
