<?php
$title = "Edit Broker";
$model = "broker";

// action for in form to execute when saved
$action = $GLOBALS['BASE'] . "/$model/update/" . $record['id'];

// Include the generic form:
include __DIR__ . "/../common/form.php";
?>
