<?php
require_once '../core/Controller.php';
require_once '../app/models/SymbolModel.php';

class SymbolController extends Controller
{
    public $model;
    public function __construct()
    {
        $this->childFileName(__FILE__);
        $this->loadModel($this->controllerName);
        $this->model = new Symbol();
    }
    public function getNotesAjx() {
        header('Content-Type: application/json');

        if (isset($_GET['symbol'])) {
            $symbol = $_GET['symbol'];

            $result = $this->model->getInfoOnSymbol($symbol);

            if ($result) {
                echo json_encode($result);
            } else {
                echo json_encode(['error' => 'Symbol not found']);
            }
        } else {
            echo json_encode(['error' => 'Symbol parameter missing']);
        }
    }

    public function updateAjx() {
        header('Content-Type: application/json');

        if (isset($_POST['symbol']) && isset($_POST['notes'])) {
            $symbol = $_POST['symbol'];
            $notes = $_POST['notes'];

            $success = $this->model->updateNotes($symbol, $notes);

            if ($success) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['error' => 'Update failed']);
            }
        } else {
            echo json_encode(['error' => 'Required parameters missing']);
        }
    }
  
}