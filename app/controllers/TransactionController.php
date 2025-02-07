<?php
require_once '../core/Controller.php';
require_once '../app/models/Exchange.php';

class TransactionController extends Controller
{
    private $exchangeModel;
    private $controllerName;

    public function __construct()
    {
        $fileName = strtolower(pathinfo(__FILE__, PATHINFO_FILENAME));
        $search = 'controller';
        if (substr($fileName, -strlen($search)) === $search) {
            $fileName = substr($fileName, 0, -strlen($search));
        }
        $this->controllerName = $fileName;
        $this->loadModel($this->controllerName);
        $this->model = new Transaction();
    }

    public function list()
    {
        $transactions = $this->model->get();
        $this->renderView($this->controllerName.'/listd', ['data' => $transactions]);
    }


    // Show form to add a new transaction
    public function create()
    {
        $this->renderView($this->controllerName.'/create');
    }

    // Handle form submission for adding a transaction
    public function store()
    {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $this->model->insert();
            header("Location: " . $GLOBALS['BASE'] . "/".$this->controllerName."/list");
            exit;
        }
    }


    // Show form to edit an existing transaction
    public function edit($id)
    {
        $transaction = $this->model->get($id);

        $this->exchangeModel = new Exchange();
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
            $this->model->update($id);
            header("Location: " . $GLOBALS['BASE'] . "/".$this->controllerName."/list");
            exit;
        }
    }


    // Delete a transaction by ID
    public function delete($id)
    {
        $this->model->delete($id);
        header("Location: " . $GLOBALS['BASE'] . "/".$this->controllerName."/list");
        exit;
    }

}
