<?php
$title = "Edit Strategy";
$model = "strategy";

// action for in form to execute when saved
$action = $GLOBALS['BASE'] . "/$model/update/" . $record['id'];

// Include the generic form:
include __DIR__ . "/../common/form.php";
?>
