<?php
require_once '../core/Model.php';

class Exchange extends GenericModel
{
    protected $table = 'exchange';

    public $tableFields = [
        'name' => [
            'type' => 's',
            'label' => 'Exchange Name',
            'input' => 'text',  // renders <input type="date">
            'required' => true
        ],
    ];
}
