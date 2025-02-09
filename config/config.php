<?php
include 'secret.php';

// Automatically detect base URL
$basePath = str_replace('/public', '', dirname($_SERVER['SCRIPT_NAME']));

// Make the variable globally accessible
$GLOBALS['BASE'] = $basePath;

function dbConnect() {
    return new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
}

