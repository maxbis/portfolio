<?php
require_once '../core/Controller.php';
require_once '../app/models/QuoteModel.php';

class QuoteController extends Controller
{
    public $model;
    private $quoteModel;
    public function __construct()
    {
        $this->childFileName(__FILE__);
        $this->loadModel($this->controllerName);
        $this->model = new Quote();
    }

  
}
?>