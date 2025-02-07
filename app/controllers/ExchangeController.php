<?php
require_once '../core/Controller.php';
require_once '../app/models/Exchange.php';

class ExchangeController extends Controller
{
    public $model;
    private $exchangeModel;
    public function __construct()
    {
        $this->childFileName(__FILE__);
        $this->loadModel($this->controllerName);
        $this->model = new Exchange();
    }

  
}