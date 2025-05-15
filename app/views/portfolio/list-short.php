<?php
// --- Define the grid columns ---
// Properties:
//   - name      : Column header label.
//   - width     : CSS width (e.g. "60px") if needed, otherwise leave empty.
//   - data      : PHP code snippet that outputs the cell value. (Be careful with eval!)
//   - aggregate : "sum" or "average" to aggregate data in the footer (or null for no aggregation).
//   - sortable  : (1/0) If the column header should be clickable for sorting.
//   - filter    : "text", "select", or "none" to control the filtering UI.
$columns = [
  [
    'name' => 'Broker<br/>&nbsp;',
    'width' => '80px',
    'data' => 'broker',
    'aggregate' => null,
    'link' => '/portfolio/list?broker={broker}',
    'sortable' => 1,
    'filter' => 'select',  // Dropdown filter.
  ],
  [
    'name' => 'Symbol<br/>&nbsp;',
    'width' => '80px',
    'data' => 'symbol',
    'title' => 'symbol_title',
    'aggregate' => null,
    'link' => '/portfolio/list?symbol={symbol}',
    'sortable' => 1,
    'filter' => 'select',  // Dropdown filter.
  ],
  [
    'name' => 'Fonds',
    'width' => '4120px',
    'data' => 'symbol_title',
    'aggregate' => null,
    'sortable' => 1,
    'filter' => 'select',
  ],
  [
    'name' => 'Quote Date',
    'width' => '80px',
    'align' => 'right',
    'data' => 'quote_date',
    'formatter' => 'isset($item["quote_date"]) && !empty($item["quote_date"]) ? date("d/m", strtotime($item["quote_date"])) : "-"',
    'aggregate' => null,
    'sortable' => 1,
    'filter' => 'text',
    'hide' => true,
  ],
  [
    'name' => 'Value<br/>EUR',
    'width' => '120px',
    'align' => 'right',
    'bgcolor' => '#f4ffe8',
    'data' => 'total_value',
    'formatter' => 'number_format($item["total_value"], 0, ".", " ")',
    'aggregate' => 'sum',  // Sum the values.
    'aggregateToken' => 'VALUE_EUR', // Custom token for formula.
    'sortable' => 1,
    'filter' => 'none',
  ],
  [
    'name' => 'Total<br> P/L (Eur)',
    'width' => '120px',
    'align' => 'right',
    'bgcolor' => '#fffdf7',
    'data' => 'profit_loss',
    'formatter' => 'number_format($item["profit_loss"], 0, ".", " ")',
    'aggregate' => 'sum',  // Sum profit/loss.
    'sortable' => 1,
    'filter' => 'none',
  ],
  [
    'name' => 'Day<br/>P/L',
    'width' => '90px',
    'align' => 'right',
    'bgcolor' => '#fffdf7',
    'data' => '{total_value} - {total_value_1d}',
    'aggregate' => '{VALUE_EUR}-{VALUE_1D}',  // Sum the values.
    'sortable' => 1,
    'filter' => 'none',  // No filter.
  ],
  [
    'name' => 'Day<br>P/L%',
    'width' => '90px',
    'align' => 'right',
    'bgcolor' => '#fffdf7',
    'color' => 'darkred',
    'data' => '({total_value}*100 / {total_value_1d}) -100',
    'aggregate' => 'sum',
    'sortable' => 1,
    'filter' => 'none',  // No filter.
  ],
  [
    'name' => 'YTD<br>P/L',
    'width' => '120px',
    'align' => 'right',
    'bgcolor' => '#f8f8f8',
    'data' => 'ytd_profit_loss',
    '_formatter' => 'number_format($item["ytd_profit_loss"], 2, ".", " ")',
    'aggregate' => 'sum',  // Sum YTD profit/loss.
    'aggregateToken' => 'YTD_PL', // Custom token for formula.
    'sortable' => 1,
    'filter' => 'none',
  ],
  [
    'name' => 'YTD P/L%',
    'width' => '60px',
    'align' => 'right',
    'bgcolor' => '#f8f8f8',
    'color' => 'darkred',
    'data' => 'profit_loss_percent',
    'formatter' => 'number_format($item["profit_loss_percent"], 2, ".", " ")',
    'aggregate' => '({YTD_PL} * 100 / ({VALUE_EUR} - {YTD_PL})) ', // Custom formula for aggregation.
    'sortable' => 1,
    'filter' => 'none'
  ],

  [
    'name' => '% of Selection',
    'align' => 'right',
    'data' => '{total_value} / {VALUE_EUR} * 100',
    'aggregate' => 'sum',  // Or "average" if desired.
    'sortable' => 1,
  ],
  [
    'name' => 'β<br>&nbsp;',
    'width' => '90px',
    'align' => 'right',
    'data' => 'beta',
  ],
  [
    'name' => 'Cash/<br>&nbsp;Div.',
    'width' => '90px',
    'align' => 'right',
    'data' => 'cash',
    'aggregate' => 'sum', 
  ],
  [
    'name' => '% of<br>Portfolio',
    'width' => '',
    'align' => 'right',
    'data' => 'percent_of_portfolio',
    'formatter' => 'number_format($item["percent_of_portfolio"], 2, ".", " ")."%"',
    'aggregate' => 'sum',  // Or "average" if desired.
    'sortable' => 1,
    'filter' => 'none',
    'hide' => true,
  ],

];

// Set additional variables
if (!isset($title)) {
  $title = 'Transaction List';
}
if (!isset($model)) {
  $model = 'portfolio';
}

$noCreate = true;

// Include the generic grid view.
include __DIR__ . '/../common/grid.php';
?>