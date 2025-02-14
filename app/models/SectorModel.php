<?php
require_once '../core/Model.php';

class Sector extends GenericModel
{
    protected $table = 'sector';

    public $tableFields = [
        'name' => [
            'type' => 's',
            'label' => 'Sector Name',
            'input' => 'text', 
            'required' => true
        ],
    ];
}