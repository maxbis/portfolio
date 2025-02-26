<?php
require_once '../core/Model.php';

class Quote extends GenericModel
{
    protected $table = 'quotes';

    public $tableFields = [
        'symbol' => [
            'label' => 'Ticker Symbol',
            'input' => 'text',
            'required' => true
        ],
        'quote_date' => [
            'label' => 'Date',
            'input' => 'text',
            'required' => true
        ],
        'close' => [
            'label' => 'Close Price',
            'input' => 'text',
            'required' => true
        ],
        'volume' => [
            'label' => 'Volume',
            'input' => 'text',
            'required' => false
        ],
        'dividends' => [
            'label' => 'Dividend',
            'input' => 'text',
            'required' => false
        ],
        'split' => [
            'label' => 'Split',
            'input' => 'text',
            'required' => false
        ],
    ];

    public function getLatest()
    {
        // Build the SELECT clause.
        $sql = "
            SELECT q.id, q.quote_date, q.symbol, q.close
            FROM quotes AS q
            JOIN (
            SELECT symbol, MAX(quote_date) AS max_date
            FROM quotes
            GROUP BY symbol
            ) AS q2
            ON q.symbol = q2.symbol AND q.quote_date = q2.max_date
            ORDER BY q.symbol ASC;
        ";

        return $this->executeSQL($sql);

    }


    public function getBySymbolAndDate($symbol, $quote_date)
    {
        $sql = "SELECT * FROM quotes WHERE symbol = ? AND quote_date <= ? ORDER BY quote_date DESC LIMIT 1";
        $results = $this->executeSQL($sql, [$symbol, $quote_date]);
        if (!$results) {
            return null;
        }
        return $results[0];
    }



    public function getBySymbol($symbol)
    {
        $sql = "SELECT * FROM {$this->table} WHERE symbol = ?";
        return $this->executeSQL($sql, [$symbol]);
    }


}
