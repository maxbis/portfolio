<?php
require_once '../core/Model.php';

class Transaction extends GenericModel
{
    protected $table = 'transaction';
    protected $tableFields = [
        'date' => 's',
        'currency' => 's',
        'amount' => 'd',
        'amount_home' => 'd',
        'number' => 'i',
        'exchange_id' => 'i',
        'description' => 's'
    ];

    // Declare the property without initializing
    protected $joins = [];

    public function __construct()
    {
        parent::__construct(); // if GenericModel has its own constructor

        // Initialize joins dynamically using $this->table
        $this->joins = [
            [
                'type' => 'LEFT JOIN',
                'table' => 'exchange AS e',
                'on' => "{$this->table}.exchange_id = e.id",
                'select' => "e.name AS exchange_name"
            ]
        ];
    }
}
