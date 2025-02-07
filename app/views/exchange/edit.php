<?php
// Example: views/transaction/edit.php

// Assume you already have fetched the transaction from the database in $transaction.
$title = "Edit Exchange Name";
$record = $exchange;
$action = $GLOBALS['BASE'] . "/exchange/update/" . $exchange['id'];
$model = "exchange";

// Include the generic form:
include __DIR__ . "/../common/form.php";

?>
