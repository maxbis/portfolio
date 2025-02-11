<?php
require_once '../core/Model.php';

class Symbol extends GenericModel
{
    protected $table = 'symbol';

    public $tableFields = [
        'symbol' => [
            'type' => 's',
            'label' => 'My Symbol',
            'input' => 'text', 
            'required' => true
        ],
        'other_symbol' => [
            'type' => 's',
            'label' => 'Foreign Symbol',
            'input' => 'text', 
            'required' => true
        ],
        'name' => [
            'type' => 's',
            'label' => 'Name',
            'input' => 'text', 
            'required' => true
        ],
    ];
}
