<?php
require_once '../core/Controller.php';
require_once '../app/models/Exchange.php';
require_once '../app/models/Broker.php';
require_once '../app/models/Strategy.php';

class TransactionController extends Controller
{
    private $exchangeModel;
    private $brokerModel;
    private $strategyModel;
    public $model;

    public function __construct()
    {
        $this->childFileName(__FILE__);
        $this->loadModel($this->controllerName);
        $this->model = new Transaction();
    }

    public function create()
    {
        $this->exchangeModel = new Exchange();
        $exchanges = $this->exchangeModel->get();

        $this->brokerModel = new Broker();
        $brokers = $this->brokerModel->get();

        $this->strategyModel = new Strategy();
        $strategies = $this->strategyModel->get();

        $this->renderView($this->controllerName . '/my_create',
        ['exchanges' => $exchanges,  'brokers' => $brokers, 'strategies' => $strategies]);
    }

    public function edit($id)
    {
        $transaction = $this->model->get($id);

        $this->exchangeModel = new Exchange();
        $exchanges = $this->exchangeModel->get();

        $this->brokerModel = new Broker();
        $brokers = $this->brokerModel->get();

        $this->strategyModel = new Strategy();
        $strategies = $this->strategyModel->get();

        if ($transaction) {
            $this->renderView('transaction/my_edit', 
            ['record' => $transaction, 'exchanges' => $exchanges, 'brokers' => $brokers, 'strategies' => $strategies]);	
        } else {
            echo "Transaction not found.";
        }
    }



}
