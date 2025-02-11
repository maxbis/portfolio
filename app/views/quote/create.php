<?php
// Example: views/transaction/create.php

$title = "Create Quote";
$action = $GLOBALS['BASE'] . "/quote/insert"; // or /create
$model = "quote";

// For creation, $record can be an empty array (or you can pre-populate defaults if needed)
$record = [];

// Include the generic form:
include __DIR__ . "/../common/form.php";
?>
