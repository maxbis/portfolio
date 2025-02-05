<?php
require_once '../core/Controller.php';
require_once '../app/models/Exchange.php';

class ExchangeController extends Controller
{
    private $exchangeModel;
    public function __construct()
    {
        $this->exchangeModel = new Exchange();
    }
    
    public function listd()
    {
        $exchanges = $this->exchangeModel->get();
        $this->renderView('exchange/listd', ['data' => $exchanges]);
    }

    public function list()
    {
        $exchanges = $this->exchangeModel->get();
        $this->renderView('exchange/listd', ['data' => $exchanges]);
    }
}