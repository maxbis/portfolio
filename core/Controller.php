<?php
class Controller
{
    public $controllerName;

    public function childFileName($childFileName)
    {
        $fileName = strtolower(pathinfo($childFileName, PATHINFO_FILENAME));
        $search = 'controller';
        if (substr($fileName, -strlen($search)) === $search) {
            $fileName = substr($fileName, 0, -strlen($search));
        }
        $this->controllerName = $fileName;
        return $fileName;
    }

    public function loadModel($model)
    {
        $model = ucfirst($model); // all models are capitalized (e.g. Portfolio)
        require_once "../app/models/" . $model . "Model.php";
        return new $model();
    }

    public function renderView($view, $data = [])
    {
        extract($data);
        require_once "../app/views/$view.php";
    }

    public function list()
    {
        $records = $this->model->get();
        $this->renderView($this->controllerName . '/list', ['data' => $records]);
    }

    // Show form to add a new transaction
    public function create()
    {
        $this->renderView($this->controllerName . '/create');
    }

    // Handle form submission for adding a transaction
    public function insert()
    {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $this->model->insert();
            header("Location: " . $GLOBALS['BASE'] . "/" . $this->controllerName . "/list");
            exit;
        }
    }


    // Show form to edit an existing transaction
    public function edit($id)
    {
        $record = $this->model->get($id);

        if ($record) {
            $this->renderView('/' . $this->controllerName . '/edit', ['record' => $record]);
        } else {
            echo "No record found to edit (invalid id).";
        }
    }

    // Handle update transaction form submission
    public function update($id)
    {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $this->model->update($id);
            header("Location: " . $GLOBALS['BASE'] . "/" . $this->controllerName . "/list");
            exit;
        }
    }


    // Delete a transaction by ID
    public function delete($id)
    {
        $this->model->delete($id);
        header("Location: " . $GLOBALS['BASE'] . "/" . $this->controllerName . "/list");
        exit;
    }

    /**
     * Generates a CSV export from an array of associative arrays.
     *
     * @param array $data The data to be exported.
     * @param array $config Optional configuration:
     *   - 'delimiter': CSV delimiter (default: comma).
     *   - 'enclosure': Field enclosure character (default: double quote).
     *   - 'localisation': An associative array mapping column keys to localized header names.
     * @return string The CSV content as a string.
     */
    public function exportExcel($data)
    {
        if (!isset($data[0])) {
            return;
        }

        header('Content-type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="canvas-export' . date('YmdHi') . '.csv"');
        // header("Pragma: no-cache");
        // header("Expires: 0");
        header('Content-Transfer-Encoding: binary');
        echo "\xEF\xBB\xBF";

        $seperator = ";"; // NL version, use , for EN

        foreach ($data[0] as $key => $value) {
            echo $key . $seperator;
        }
        echo "\n";
        foreach ($data as $line) {
            foreach ($line as $key => $value) {
                // echo preg_replace('/[\s+,;]/', ' ', $value) . $seperator;
                echo  "\"". $value ."\"". $seperator;
            }
            echo "\n";
        }
    }


}