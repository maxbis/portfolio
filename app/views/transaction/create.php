<?php
// Example: views/transaction/create.php

$title = "Create Transaction";
$action = $GLOBALS['BASE'] . "/transaction/insert"; // or /create
$model = "transaction";

// For creation, $record can be an empty array (or you can pre-populate defaults if needed)
$record = [];

// Include the generic form:
include __DIR__ . "/../common/form.php";
?>
