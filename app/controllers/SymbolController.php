<?php
require_once '../core/Controller.php';
require_once '../app/models/Symbol.php';

class SymbolController extends Controller
{
    public $model;
    private $symbolModel;
    public function __construct()
    {
        $this->childFileName(__FILE__);
        $this->loadModel($this->controllerName);
        $this->model = new Symbol();
    }

  
}