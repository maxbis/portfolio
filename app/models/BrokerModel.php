<?php
require_once '../core/Model.php';

class Broker extends GenericModel
{
    protected $table = 'broker';

    public $tableFields = [
        'name' => [
            'type' => 's',
            'label' => 'Broker Name',
            'input' => 'text', 
            'required' => true
        ],
        'short_name' => [
            'type' => 's',
            'label' => 'Short Name',
            'input' => 'text', 
            'required' => true
        ],
    ];
}