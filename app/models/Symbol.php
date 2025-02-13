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
        'notes' => [
            'type' => 's',
            'label' => 'Notes',
            'input' => 'textarea',
            'rows' => 5,
            'class' => 'text-sm',
            'placeholder' => 'Enter your comments here...',
            'required' => false
        ],
        'active' => [
            'type' => 'i',
            'label' => 'Active',
            'input' => 'select',
            'options' => [
                '1' => 'active',
                '0' => 'inactive',
            ],
            'required' => false
        ],
    ];
}
