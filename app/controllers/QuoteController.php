<?php
require_once '../core/Controller.php';
require_once '../app/models/QuoteModel.php';

class QuoteController extends Controller
{
    public $model;
    public function __construct()
    {
        $this->childFileName(__FILE__);
        $this->loadModel($this->controllerName);
        $this->model = new Quote();
    }

    public function list($symbol = null)
    {
        if ($symbol) {
            $records = $this->model->getBySymbol($symbol);
        } else {
            $records = $this->model->getLatest();
        }
        $this->renderView(
            'quote/list',
            ['title' => 'Quote', 'data' => $records]
        );
    }

    public function listQuote($symbol)
    {
        $quotes = $this->model->getQuotesBySymbol($symbol);
        $this->renderView('quote/list', ['quotes' => $quotes]);
    }

    public function graph($symbol)
    {
        $data = $this->model->getBySymbol($symbol);
        $this->renderView('quote/graph', ['data' => $data]);
    }


    public function getApiClosePrice($symbol, $quote_date)
    {
        header('Content-Type: application/json');

        if (!$symbol || !$quote_date) {
            echo json_encode(['error' => 'Invalid parameters']);
            return;
        }

        // Create an instance of the Quote mode
        $quoteModel = new Quote();
        $quote = $quoteModel->getBySymbolAndDate($symbol, $quote_date);

        // print_r($quote);
        // exit;

        if ($quote) {
            // Assuming the quotes table has a 'close' field
            echo json_encode(['close' => $quote['close']]);
        } else {
            echo json_encode(['close' => null]);
        }
    }

    public function graph2($symbol)
    {
        $data = $this->model->getBySymbol($symbol);
        $this->renderView('quote/graph2', ['data' => $data]);
    }


}
?>