<?php
require_once '../core/Controller.php';
require_once '../app/models/Exchange.php';

class TransactionController extends Controller
{
    private $exchangeModel;
    public $model;

    public function __construct()
    {
        $this->childFileName(__FILE__);
        $this->loadModel($this->controllerName);
        $this->model = new Transaction();
    }

    public function edit($id)
    {
        $transaction = $this->model->get($id);

        $this->exchangeModel = new Exchange();
        $exchanges = $this->exchangeModel->get();

        if ($transaction) {
            $this->renderView('transaction/edit', ['record' => $transaction, 'exchanges' => $exchanges]);
        } else {
            echo "Transaction not found.";
        }
    }

    
    public function listd()
    {
        $records = $this->model->get();
        $this->renderView($this->controllerName.'/listd', ['data' => $records]);
    }


}
