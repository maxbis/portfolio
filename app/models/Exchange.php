<?php
require_once '../core/Model.php';

class Exchange extends GenericModel
{
    protected $table = 'exchange';
    protected $tableFields = [
        'exchange_name' => 's',
    ];

}
