<?php

// Define your columns including the edit column

$columns = [
  [
    'name' => 'Name',
    'width' => '100px',
    'data' => 'name',
    'aggregate' => null,
    'sortable' => 1,
    'filter' => 'text',
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
  $title = 'Sector List';
}
if (!isset($model)) {
  $model = 'sector';
}

// Include the generic grid view.
include __DIR__ . '/../common/grid.php';