<?php

// Define your columns including the edit column

$columns = [
  [
    'name' => 'symbol',
    'width' => '100px',
    'data' => 'symbol',
    'link' => '/quote/list/{symbol}',
    'aggregate' => null,
    'sortable' => 1,
    'filter' => 'select',
  ],
  [
    'name' => 'Date',
    'width' => '100px',
    'data' => 'quote_date',
    'aggregate' => null,
    'sortable' => 1,
    'filter' => 'select',
    'align' => 'left',
  ],
  [
    'name' => 'Close',
    'width' => '100px',
    'data' => 'close',
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
  $title = 'Latest Quotes';
}
if (!isset($model)) {
  $model = 'quote';
}

// Include the generic grid view.
include __DIR__ . '/../common/grid.php';