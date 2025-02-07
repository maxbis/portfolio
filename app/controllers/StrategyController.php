<?php
require_once '../core/Controller.php';
require_once '../app/models/Strategy.php';

class StrategyController extends Controller
{
    public $model;
    private $strategyModel;
    public function __construct()
    {
        $this->childFileName(__FILE__);
        $this->loadModel($this->controllerName);
        $this->model = new Strategy();
    }

  
}