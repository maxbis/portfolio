<?php
class Controller {
    private $model;
    public function loadModel($model) {
        require_once "../app/models/$model.php";
        return new $model();
    }
    
    public function renderView($view, $data = []) {
        extract($data);
        require_once "../app/views/$view.php";
    }
    
}