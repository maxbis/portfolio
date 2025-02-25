<?php
require_once '../core/Model.php';

class Strategy extends GenericModel
{
    protected $table = 'strategy';

    public $tableFields = [
        'name' => [
            'type' => 's',
            'label' => 'Strategy Name',
            'input' => 'text', 
            'required' => true
        ],
    ];
    public function getStrategiesBySymbol($symbol)
    {
        $sql = "SELECT * FROM strategy WHERE id IN (SELECT strategy_id FROM transaction WHERE symbol = :symbol)";
        $this->executeSQL($sql, ['symbol' => $symbol]);
    }
}
