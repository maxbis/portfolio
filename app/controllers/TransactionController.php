<?php
require_once '../core/Controller.php';
require_once '../app/models/Transaction.php';

class TransactionController extends Controller {
    private $transactionModel;

    public function __construct() {
        $this->transactionModel = new Transaction();
    }

    // List all transactions
    public function list() {
        $transactions = $this->transactionModel->getAll();
        $this->renderView('transaction/list', ['transactions' => $transactions]);
    }

    // Show form to add a new transaction
    public function create() {
        $this->renderView('transaction/create');
    }

    // Handle form submission for adding a transaction
    public function store() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $amount = $_POST['amount'];
            $number = $_POST['number'];
            $symbol = $_POST['symbol'];
            $exchange = $_POST['exchange'];
            $description = $_POST['description'];

            $this->transactionModel->insert($amount, $number, $symbol, $exchange, $description);
            header("Location: " . $GLOBALS['BASE'] . "/transaction/list");
            exit;
        }
    }

    // Show form to edit an existing transaction
    public function edit($id) {
        $transaction = $this->transactionModel->getById($id);
        if ($transaction) {
            $this->renderView('transaction/edit', ['transaction' => $transaction]);
        } else {
            echo "Transaction not found.";
        }
    }

    // Handle update transaction form submission
    public function update($id) {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $date        = $_POST['date'];  
            $amount      = $_POST['amount'];
            $number      = $_POST['number'];
            $symbol      = $_POST['symbol'];
            $exchange    = $_POST['exchange'];
            $description = $_POST['description'];
    
            $this->transactionModel->update($id, $date, $amount, $number, $symbol, $exchange, $description);
            header("Location: " . $GLOBALS['BASE'] . "/transaction/list");
            exit;
        }
    }
    
      // Delete a transaction by ID
      public function delete($id) {
        $this->transactionModel->delete($id);
        header("Location: " . $GLOBALS['BASE'] . "/transaction/list");
        exit;
    }

}
