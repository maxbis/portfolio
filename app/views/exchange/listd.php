<?php

// Define your columns including the edit column

$columns = [
  [
    'name' => 'id',
    'width' => '100px',
    'data' => 'id',
    'aggregate' => null,
    'sortable' => 1,
    'filter' => 'text',
  ],
  [
    'name' => 'Exchange',
    'width' => '100px',
    'data' => 'name',
    'aggregate' => null,
    'sortable' => 1,
    'filter' => 'select',
    'align' => 'left',
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
  $title = 'Exchange List';
}
if (!isset($model)) {
  $model = 'exchange';
}

// Include the generic grid view.
include __DIR__ . '/../common/grid.php';