<?php
class Controller {
    public $controllerName;

    public function childFileName($childFileName) {
        $fileName = strtolower(pathinfo($childFileName, PATHINFO_FILENAME));
        $search = 'controller';
        if (substr($fileName, -strlen($search)) === $search) {
            $fileName = substr($fileName, 0, -strlen($search));
        }
        $this->controllerName = $fileName;
        return $fileName;
    }

    public function loadModel($model) {
        $model = ucfirst($model); // all models are capitalized (e.g. Portfolio)
        require_once "../app/models/".$model."Model.php";
        return new $model();
    }
    
    public function renderView($view, $data = []) {
        extract($data);
        require_once "../app/views/$view.php";
    }

    public function list()
    {
        $records = $this->model->get();
        $this->renderView($this->controllerName.'/list', ['data' => $records]);
    }

    // Show form to add a new transaction
    public function create()
    {
        $this->renderView($this->controllerName.'/create');
    }

    // Handle form submission for adding a transaction
    public function insert()
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
        $record = $this->model->get($id);

        if ($record) {
            $this->renderView('/'.$this->controllerName.'/edit', ['record' => $record]);
        } else {
            echo "No record found to edit (invalid id).";
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