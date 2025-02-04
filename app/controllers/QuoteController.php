<?php
require_once '../core/Controller.php';
require_once '../app/models/Quote.php';

class QuoteController extends Controller
{
    private $quoteModel;

    public function __construct()
    {
        $this->quoteModel = new Quote();
    }

    public function listQuote($symbol)
    {
        $quotes = $this->quoteModel->getQuotesBySymbol($symbol);
        $this->renderView('quote/list', ['quotes' => $quotes]);
    }

    public function graph($symbol)
    {
        $quotes = $this->quoteModel->getQuotesBySymbol($symbol);
        $this->renderView('quote/graph', ['quotes' => $quotes]);
    }


    public function getApiClosePrice($symbol, $quote_date)
    {
        header('Content-Type: application/json');
    
        if (!$symbol || !$quote_date) {
            echo json_encode(['error' => 'Invalid parameters']);
            return;
        }
    
        // Create an instance of the Quote model
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
    

}

?>