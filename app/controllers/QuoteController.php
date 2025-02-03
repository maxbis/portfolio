<?php
require_once '../core/Controller.php';
require_once '../app/models/Quote.php';

class QuoteController extends Controller {
    private $quoteModel;

    public function __construct() {
        $this->quoteModel = new Quote();
    }

    public function listQuote($symbol) {
        $quotes = $this->quoteModel->getQuotesBySymbol($symbol);
        $this->renderView('quote/list', ['quotes' => $quotes]);
    }

    public function graph($symbol) {
        $quotes = $this->quoteModel->getQuotesBySymbol($symbol);
        $this->renderView('quote/graph', ['quotes' => $quotes]);
    }
}

?>
