<?php

// Define your columns including the edit column

$columns = [
  [
    'name' => 'id',
    'width' => '140px',
    'data' => 'date',
    'aggregate' => null,
    'sortable' => 1,
    'filter' => 'text',
  ],
  [
    'name' => 'Broker',
    'width' => '40px',
    'data' => 'broker_short_name',
    'aggregate' => null,
    'sortable' => 1,
    'filter' => 'select',
  ],
  [
    'name' => '#',
    'width' => '40px',
    'data' => 'number',
    'aggregate' => null,
    'sortable' => 1,
    'filter' => 'none',
    'align' => 'right',
  ],
  [
    'name' => 'Amount',
    'width' => '120px',
    'data' => 'amount',	
    'aggregate' => null,
    'sortable' => 1,
    'filter' => 'text',
    'align' => 'right',
  ],
  [
    'name' => 'Currency',
    'width' => '120px',
    'data' => 'currency',	
    'aggregate' => null,
    'sortable' => 1,
    'filter' => 'text',
    'align' => 'left',
  ],
  [
    'name' => 'Symbol',
    'width' => '100px',
    'data' => 'symbol',
    'aggregate' => null,
    'sortable' => 1,
    'filter' => 'text',
  ],
  [
    'name' => 'Exchange',
    'width' => '',
    'data' => 'exchange_name',
    'aggregate' => null,
    'sortable' => 1,
    'filter' => 'select',
  ],
  [
    'name'      => '',
    'width'     => '60px',
    'data'      => '#edit',
    'aggregate' => null,
    'sortable'  => 0,
    'filter'    => 'none',
  ]
];

// Set additional variables
if (!isset($title)) {
  $title = 'Transaction List';
}
if (!isset($model)) {
  $model = 'transaction';
}
// Include the generic grid view.
include __DIR__ . '/../common/grid.php';