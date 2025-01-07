<?php

require_once(__DIR__ . '/src/RtbBidRequestHandler.php');

// Instantiate and handle the bid request
$handler = new RtbBidRequestHandler();
$handler->handleBidRequest();

?>
