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
        $exchanges = $this->exchangeModel->getAllSorted(['name'=>'ASC']);

        $this->brokerModel = new Broker();
        $brokers = $this->brokerModel->getAllSorted(['name'=>'ASC']);

        $this->strategyModel = new Strategy();
        $strategies = $this->strategyModel->getAllSorted(['name'=>'ASC']);

        $this->renderView($this->controllerName . '/my_create', ['exchanges' => $exchanges,  'brokers' => $brokers, 'strategies' => $strategies]);
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

    public function list()
    {
        $records = $this->model->getAllSorted(['id'=>'DESC']);
        foreach ($records as &$record) {
            $record['investment'] = number_format($record['amount_home'] * $record['number'] - $record['cash'], 2, '.', '');
        }
        // echo "<pre>";
        // print_r($records);exit;
        $this->renderView($this->controllerName.'/list', ['data' => $records]);
    }

}
