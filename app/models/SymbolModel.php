<?php
require_once '../core/Model.php';

class Symbol extends GenericModel
{
    protected $table = 'symbol';

    public $tableFields = [
        'symbol' => [
            'label' => 'My Symbol',
            'input' => 'text',
            'required' => true
        ],
        'other_symbol' => [
            'label' => 'Foreign Symbol',
            'input' => 'text',
            'required' => true
        ],
        'name' => [
            'label' => 'Name',
            'input' => 'text',
            'required' => true
        ],
        'sector_id' => [
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
            'label' => 'Beta',
            'input' => 'text',
            'required' => false,
            'default' => 1,
        ],
        'notes' => [
            'label' => 'Notes',
            'input' => 'textarea',
            'rows' => 5,
            'class' => 'text-sm',
            'placeholder' => 'Enter your comments here...',
            'required' => false
        ],
        'risk' => [
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

    public function updateNotes($symbol, $notes)
    {
        $sql = "UPDATE symbol SET notes = :notes WHERE symbol = :symbol";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':notes' => $notes, ':symbol' => $symbol]);
    }
 
}
