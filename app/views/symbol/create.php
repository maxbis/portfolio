<?php
// Example: views/transaction/create.php

$title = "Create Instrument";
$model = "symbol";


$action = $GLOBALS['BASE'] . "/$model/insert"; // or /create

// For creation, $record can be an empty array (or you can pre-populate defaults if needed)
$record = [];

// Include the generic form:
include __DIR__ . "/../common/form.php";
?>
