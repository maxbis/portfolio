<?php
require_once '../core/Model.php';

class Transaction extends GenericModel
{
    protected $table = 'transaction';
    public $tableFields = [
        'date' => [
            'type' => 's',
            'label' => 'Date',
            'input' => 'date',  // renders <input type="date">
            'required' => true
        ],
        'symbol' => [
            'type' => 's',
            'label' => 'Symbol',
            'input' => 'text',
            'required' => true
        ],
        'currency' => [
            'type' => 's',
            'label' => 'Currency',
            'input' => 'select', // renders a <select>
            'required' => true,
            // if not a foreign key, you can supply your own options:
            'options' => [
                'EUR'    => 'EUR',
                'USD' => 'USD'
            ]
        ],
        'amount' => [
            'type' => 'd',
            'label' => 'Price',
            'input' => 'text',
            'required' => true
        ],
        'amount_home' => [
            'type' => 'd',
            'label' => 'Price EU',
            'input' => 'text',
            'readonly' => false  // we want to show this but not allow editing
        ],
        'number' => [
            'type' => 'i',
            'label' => 'Number',
            'input' => 'text',
            'required' => true
        ],
        'exchange_id' => [
            'type' => 'i',
            'label' => 'Exchange',
            'input' => 'select',
            'required' => true,
            // Indicate that this is a foreign key.
            'foreign' => [
                'model'      => 'Exchange',
                'valueField' => 'id',
                'textField'  => 'name',
                'alias' => 'e' // optional alias for the joined table
            ]
        ],
        'broker_id' => [
            'type' => 'i',
            'label' => 'Broker',
            'input' => 'select',
            'required' => true,
            // Indicate that this is a foreign key.
            'foreign' => [
                'model'      => 'Broker',
                'valueField' => 'id',
                'textField'  => 'short_name',
                'alias' => 'b' // optional alias for the joined table
            ]
        ],
        'description' => [
            'type' => 's',
            'label' => 'Description',
            'input' => 'textarea'
        ]
    ];

    // Declare the property without initializing
    protected $joins = [];

    public function __construct()
    {
        parent::__construct(); // if GenericModel has its own constructor

        // Initialize joins dynamically using $this->table
   
    }

    public function insert()
    {
        // If amount_home is not set, set it to amount
        if (empty($_POST['amount_home'])) {
            $_POST['amount_home'] = $_POST['amount'];
        }

        // Continue wiht the parent method
        parent::insert();
    }

}
