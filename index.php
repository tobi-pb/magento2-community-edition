<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

require_once __DIR__ . '/app/bootstrap.php';

$status = new \Magento\Update\Status();

$statusMessage = $status->get(10);
$isUpdateInProgress = $status->isUpdateInProgress();

/** TODO: Section below is added for demo purposes */
$status->add("Message #" . rand(1, 1000));
if (!$statusMessage) {
    $statusMessage = 'Please wait for job processing to start.';
}
$statusMessage = str_replace("\n", "<br />", $statusMessage);
//$isUpdateInProgress = (bool)rand(0, 1);
/** TODO: End of section added for demo */

if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    /* Ajax request processing */
    echo json_encode(['statusMessage' => $statusMessage, 'isUpdateInProgress' => $isUpdateInProgress]);
} else {
    include __DIR__ . '/app/code/Magento/Update/view/templates/status.phtml';
}
