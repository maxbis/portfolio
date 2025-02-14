<?php
require_once '../core/Controller.php';
require_once '../app/models/SectorModel.php';

class SectorController extends Controller
{
    public $model;
    private $sectorModel;
    public function __construct()
    {
        $this->childFileName(__FILE__);
        $this->loadModel($this->controllerName);
        $this->model = new Sector();
    }

  
}