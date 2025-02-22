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
        'sector_id' => [
            'type' => 's',
            'label' => 'Sector',
            'input' => 'select',
            'required' => true,
            'foreign' => [
                'model'      => 'Sector',
                'valueField' => 'id',
                'textField'  => 'name',
                'alias' => 'se' // optional alias for the joined table
            ]
        ],
        'beta' => [
            'type' => 'd',
            'label' => 'Beta',
            'input' => 'text',
            'required' => false
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
        'risk' => [
            'type' => 'i',
            'label' => 'Risk',
            'input' => 'select',
            'options' => [
                'A' => 'A',
                'B' => 'B',
                'C' => 'C',
                'D' => 'D',
                'E' => 'E',
                'F' => 'F',
            ],
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

    public function getInfoOnSymbol($symbol)
    {
        $sql = "SELECT * FROM symbol WHERE symbol = :symbol";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':symbol' => $symbol]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
