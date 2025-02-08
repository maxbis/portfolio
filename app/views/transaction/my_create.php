<?php
// filepath: /c:/Users/maxbi/www/portfolio/app/views/transaction/my_create.php

$title = "Create Transaction";
$action = $GLOBALS['BASE'] . "/transaction/insert";
$record = []; // Empty record for creating a new transaction
include __DIR__ . "/transaction_form.php";
?>