<?php
require_once '../core/Controller.php';
require_once '../app/models/Transaction.php';
require_once '../app/models/Exchange.php';

class TransactionController extends Controller
{
    private $transactionModel;
    private $exchangeModel;

    public function __construct()
    {
        $this->transactionModel = new Transaction();
        $this->exchangeModel = new Exchange();
    }

    public function listd()
    {
        $transactions = $this->transactionModel->get();
        $this->renderView('transaction/listd', ['data' => $transactions]);
    }

    // List all transactions
    public function list()
    {
        $transactions = $this->transactionModel->get();
        $this->renderView('transaction/list', ['transactions' => $transactions]);
    }

    // Show form to add a new transaction
    public function create()
    {
        $this->renderView('transaction/create');
    }

    // Handle form submission for adding a transaction
    public function store()
    {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $this->transactionModel->insert();
            header("Location: " . $GLOBALS['BASE'] . "/transaction/list");
            exit;
        }
    }


    // Show form to edit an existing transaction
    public function edit($id)
    {
        $transaction = $this->transactionModel->get($id);
        $exchanges = $this->exchangeModel->get();

        if ($transaction) {
            $this->renderView('transaction/edit', ['transaction' => $transaction, 'exchanges' => $exchanges]);
        } else {
            echo "Transaction not found.";
        }
    }

    // Handle update transaction form submission
    public function update($id)
    {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $this->transactionModel->update($id);
            header("Location: " . $GLOBALS['BASE'] . "/transaction/list");
            exit;
        }
    }


    // Delete a transaction by ID
    public function delete($id)
    {
        $this->transactionModel->delete($id);
        header("Location: " . $GLOBALS['BASE'] . "/transaction/list");
        exit;
    }

}
