<?php
require_once '../core/Model.php';

class Exchange extends GenericModel
{
    protected $table = 'exchange';

    public $tableFields = [
        'name' => [
            'label' => 'Exchange Name',
            'input' => 'text', 
            'required' => true
        ],
        'currency' => [
            'label' => 'Currency',
            'input' => 'select',
            'options' => [
                'EUR' => 'EUR',
                'USD' => 'USD'
            ],
            'required' => true
        ],
    ];

    public function getExchangesBySymbol($symbol)
    {
        $sql = "SELECT * FROM exchange WHERE id IN (SELECT exchange_id FROM transaction WHERE symbol = :symbol)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['symbol' => $symbol]);
        return $stmt->fetchAll();
    }
}
