<?php
// filepath: /c:/Users/maxbi/www/portfolio/app/views/transaction/my_edit.php

$title = "Edit Transaction";
$action = $GLOBALS['BASE'] . "/transaction/update/" . $record['id'];
include __DIR__ . "/transaction_form.php";
?>