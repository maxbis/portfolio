<?php
// Example: views/transaction/edit.php

// Assume you already have fetched the transaction from the database in $transaction.
$title = "Edit Transaction";
$record = $transaction;
$action = $GLOBALS['BASE'] . "/transaction/update/" . $transaction['id'];
$model = "transaction";

// Include the generic form:
include __DIR__ . "/../common/form.php";

?>
