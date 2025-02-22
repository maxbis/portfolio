<?php
require_once '../core/Model.php';

class Broker extends GenericModel
{
    protected $table = 'broker';

    public $tableFields = [
        'name' => [
            '_type' => 's',
            'label' => 'Broker Name',
            'input' => 'text', 
            'required' => true
        ],
        'short_name' => [
            '_type' => 's',
            'label' => 'Short Name',
            'input' => 'text', 
            'required' => true
        ],
    ];

    public function getBrokersBySymbol($symbol)
    {
        $sql = "SELECT * FROM broker WHERE id IN (SELECT broker_id FROM transaction WHERE symbol = :symbol)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['symbol' => $symbol]);
        return $stmt->fetchAll();
    }
}