<?php
require_once '../core/Controller.php';
require_once '../app/models/Broker.php';

class BrokerController extends Controller
{
    public $model;
    private $brokerModel;
    public function __construct()
    {
        $this->childFileName(__FILE__);
        $this->loadModel($this->controllerName);
        $this->model = new Broker();
    }

  
}