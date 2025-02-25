<?php
require_once '../core/Model.php';

class Broker extends GenericModel
{
    protected $table = 'broker';

    public $tableFields = [
        'name' => [
            'label' => 'Broker Name',
            'input' => 'text', 
            'required' => true
        ],
        'short_name' => [
            'label' => 'Short Name',
            'input' => 'text', 
            'required' => true
        ],
    ];

    public function getBrokersBySymbol($symbol)
    {
        $sql = "SELECT * FROM broker WHERE id IN (SELECT broker_id FROM transaction WHERE symbol = :symbol)";
        return $this->executeSQL($sql, ['symbol' => $symbol]);
    }
}