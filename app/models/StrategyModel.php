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
}
