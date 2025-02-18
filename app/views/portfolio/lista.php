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
    'name' => 'Index<br/>&nbsp;',
    'width' => '80px',
    'data' => 'symbol',
    'title' => 'symbol_title',
    'aggregate' => null,
    'link' => '/portfolio/list?broker={symbol}',
    'sortable' => 1,
    'filter' => 'select',  // Dropdown filter.
  ],

  [
    'name' => '#<br/>&nbsp;',
    'width' => '60px',
    'align' => 'right',
    'data' => 'number',
    'aggregate' => null,
    'sortable' => 1,
    'filter' => 'none',  // Text input filter.
  ],
  [
    'name' => 'Avg Price',
    'width' => '90px',
    'align' => 'right',
    'data' => 'avg_buy_price',
    'formatter' => 'number_format($item["avg_buy_price"], 2, ".", " ")',
    'aggregate' => null,
    'sortable' => 1,
    'filter' => 'text',
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
  ],
  [
    'name' => 'Quote<br/>&nbsp;',
    'width' => '90px',
    'align' => 'right',
    'data' => 'latest_price',
    'formatter' => 'number_format($item["latest_price"], 2, ".", " ")',
    'aggregate' => null,
    'sortable' => 1,
    'filter' => 'none',  // No filter.
  ],
  [
    'name' => 'Exch Rate',
    'width' => '40px',
    'align' => 'right',
    'data' => 'exchange_rate',
    'formatter' => 'number_format($item["exchange_rate"], 2, ".", " ")',
    'aggregate' => null,
    'sortable' => 1,
    'filter' => 'none',  // No filter.
  ],
  [
    'name' => 'Value<br/>EUR',
    'width' => '120px',
    'align' => 'right',
    'data' => 'total_value',
    'formatter' => 'number_format($item["total_value"], 2, ".", " ")',
    'aggregate' => 'sum',  // Sum the values.
    'aggregateToken' => 'VALUE_EUR', // Custom token for formula.
    'sortable' => 1,
    'filter' => 'none',
  ],
  [
    'name' => 'Total<br> P/L (Eur)',
    'width' => '120px',
    'align' => 'right',
    'data' => 'profit_loss',
    'formatter' => 'number_format($item["profit_loss"], 2, ".", " ")',
    'aggregate' => 'sum',  // Sum profit/loss.
    'sortable' => 1,
    'filter' => 'none',
  ],
  [
    'name' => 'Day<br>P/L',
    'width' => '90px',
    'align' => 'right',
    'bgcolor' => '#f8f8f8',
    'data' => '{total_value} - {total_value_1d}',
    'aggregate' => 'sum',
    'aggregateToken' => 'DAY_PL', // Custom token for formula.
    'sortable' => 1,
    'filter' => 'none',  // No filter.
  ],
  [
    'name' => 'Day<br>P/L%',
    'width' => '90px',
    'bgcolor' => '#f8f8f8',
    'align' => 'right',
    'color' => 'darkred',
    'data' => '({total_value}*100 / {total_value_1d}) -100',
    'aggregate' => '({DAY_PL} / {VALUE_EUR}) * 100',
    'sortable' => 1,
    'filter' => 'none',  // No filter.
  ],
  [
    'name' => 'YTD P/L EUR',
    'width' => '120px',
    'bgcolor' => '#f8f8f8',
    'align' => 'right',
    'data' => 'ytd_profit_loss',
    'formatter' => 'number_format($item["ytd_profit_loss"], 2, ".", " ")',
    'aggregate' => 'sum',  // Sum YTD profit/loss.
    'aggregateToken' => 'YTD_PL', // Custom token for formula.
    'sortable' => 1,
    'filter' => 'none',
  ],
  [
    'name' => 'YTD PL%',	
    'width' => '60px',
    'color' => 'darkred',
    'bgcolor' => '#f8f8f8',
    'align' => 'right',
    'data' => 'profit_loss_percent',
    'aggregate' => '({YTD_PL} / {VALUE_EUR}) * 100', // Custom formula for aggregation.
    'sortable' => 1,
    'filter' => 'none',
  ],

  [
    'name' => '% of Portfolio<br>&nbsp;',
    'width' => '',
    'align' => 'right',
    'data' => 'percent_of_portfolio',
    'formatter' => '$item["percent_of_portfolio"]."%"',
    'aggregate' => 'sum',  // Or "average" if desired.
    'sortable' => 1,
    'filter' => 'none',
  ],
  // [
  //   'name' => 'Cash<br/>EUR',
  //   'width' => '120px',
  //   'align' => 'right',
  //   'data' => 'cash',
  //   'formatter' => 'number_format($item["cash"], 2, ".", " ")',
  //   'aggregate' => 'sum',  // Sum the values.
  //   'sortable' => 1,
  //   'filter' => 'none',
  // ],
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