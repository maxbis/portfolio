<?php
require_once '../core/Model.php';

class Quote extends GenericModel
{
    protected $table = 'quotes';

    public $tableFields = [
        'symbol' => [
            'type' => 's',
            'label' => 'Ticker Symbol',
            'input' => 'text',
            'required' => true
        ],
        'quote_date' => [
            'type' => 'd',
            'label' => 'Date',
            'input' => 'text',
            'required' => true
        ],
        'close' => [
            'type' => 'd',
            'label' => 'Close Price',
            'input' => 'text',
            'required' => true
        ],
        'volume' => [
            'type' => 'i',
            'label' => 'Volume',
            'input' => 'text',
            'required' => false
        ],
        'dividends' => [
            'type' => 'd',
            'label' => 'Dividend',
            'input' => 'text',
            'required' => false
        ],
        'split' => [
            'type' => 'i',
            'label' => 'Split',
            'input' => 'text',
            'required' => false
        ],
    ];

    public function get($id = null)
    {
        // Build the SELECT clause.
        $sql = "SELECT {$this->table}.* FROM {$this->table}";
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

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            $errorInfo = $this->conn->errorInfo();
            throw new Exception("Failed to prepare statement: " . $errorInfo[2]);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

}
