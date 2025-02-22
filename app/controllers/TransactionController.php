<?php
require_once '../core/Controller.php';
require_once '../app/models/ExchangeModel.php';
require_once '../app/models/BrokerModel.php';
require_once '../app/models/StrategyModel.php';
require_once '../app/models/SymbolModel.php';

class TransactionController extends Controller
{
    private $exchangeModel;
    private $brokerModel;
    private $strategyModel;
    private $symbolModel;
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

        $this->symbolModel = new Symbol();
        $symbols = $this->symbolModel->getAllSorted(['name'=>'ASC']);

        $this->renderView($this->controllerName . '/create', 
        ['exchanges' => $exchanges,  'brokers' => $brokers, 'strategies' => $strategies, 'symbols' => $symbols]);
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
            $this->renderView('transaction/edit', 
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

    public function buy($symbol)
    {
        $data = $this->getSymbolData($symbol);
        $data['caller'] = 'buy';
        $this->renderView($this->controllerName . '/buy', $data);
    }

    public function sell($symbol)
    {
        $data = $this->getSymbolData($symbol);
        $data['caller'] = 'sell';
        $this->renderView($this->controllerName . '/buy', $data);
    }

    public function dividend($symbol)
    {
        $data = $this->getSymbolData($symbol);
        $data['caller'] = 'dividend';
        $this->renderView($this->controllerName . '/dividend', $data);
    }
    
    // getSymbolData is a private function that gets the data for a symbol
    // used in the buy, sell, and dividend functions
    private function getSymbolData($symbol)
    {
        $this->brokerModel = new Broker();
        $brokers = $this->brokerModel->getBrokersBySymbol($symbol);
        $this->exchangeModel = new Exchange();
        $exchanges = $this->exchangeModel->getExchangesBySymbol($symbol);
        $this->strategyModel = new Strategy();
        $strategies = $this->strategyModel->getStrategiesBySymbol($symbol);
        $this->symbolModel = new Symbol();
        $symbol = $this->symbolModel->getInfoOnSymbol($symbol);
        return ['brokers' => $brokers, 'exchanges' => $exchanges, 'strategies' => $strategies, 'symbol' => $symbol];
    }
}
