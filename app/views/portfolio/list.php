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
    'name' => 'Strategy<br/>&nbsp;',
    'width' => '40px',
    'data' => 'strategy',
    'aggregate' => null,
    'link' => '/portfolio/list?strategy={strategy}',
    'sortable' => 1,
    'filter' => 'select',
  ],
  [
    'name' => '#<br/>&nbsp;',
    'width' => '60px',
    'align' => 'right',
    'data' => 'number',
    'aggregate' => null,
    'aggregateToken' => 'NUMBER', // Custom token for formula.
    'sortable' => 1,
    'filter' => 'none',
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
    'name' => 'Avg Price',
    'width' => '90px',
    'align' => 'right',
    'data' => 'avg_buy_price',
    'formatter' => 'number_format($item["avg_buy_price"], 2, ".", " ")',
    'aggregate' => null,
    'sortable' => 1,
    'filter' => 'none',
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
    'name' => 'Quote -1d',
    'width' => '90px',
    'align' => 'right',
    'data' => 'latest_price_1d',
    'aggregateToken' => 'QUOTE_1D', // Custom token for formula.
  ],
  [
    'name' => 'Quote<br/>&nbsp;',
    'width' => '90px',
    'align' => 'right',
    'data' => 'latest_price',
    'formatter' => 'number_format($item["latest_price"], 2, ".", " ")',
    'aggregate' => null,
    'aggregateToken' => 'QUOTE_A', // Custom token for formula.
    'sortable' => 1,
    'filter' => 'none',  // No filter.
  ],
  [
    'name' => 'Value<br/>EUR',
    'width' => '90px',
    'align' => 'right',
    'data' => 'total_value',
    'aggregate' => 'sum',  // Sum the values.
    'aggregateToken' => 'VALUE_EUR', // Custom token for formula.
    'sortable' => 1,
    'filter' => 'none',
  ],
  [
    'name' => 'Total Value -1d',
    'data' => 'total_value_1d',
    'aggregateToken' => 'VALUE_1D', // Custom token for formula.
    'aggregate' => 'sum',  // Sum the values.
    'hide' => true,  // Hide this column by default.
  ],
  [
    'name' => 'Day<br/>P/L',
    'width' => '90px',
    'align' => 'right',
    'data' => '{total_value} - {total_value_1d}',
    'aggregate' => '{VALUE_EUR}-{VALUE_1D}',  // Sum the values.
    'sortable' => 1,
    'filter' => 'none',  // No filter.
  ],
  [
    'name' => 'Day<br>P/L%',
    'width' => '90px',
    'align' => 'right',
    'data' => '({total_value}*100 / {total_value_1d}) -100',
    'aggregate' => 'sum',
    'sortable' => 1,
    'filter' => 'none',  // No filter.
  ],
  [
    'name' => 'YTD P/L',
    'width' => '120px',
    'align' => 'right',
    'data' => 'ytd_profit_loss',
    'formatter' => 'number_format($item["ytd_profit_loss"], 0, ".", " ")',
    'aggregate' => 'sum',  // Sum YTD profit/loss.
    'aggregateToken' => 'YTD_PL', // Custom token for formula.
    'sortable' => 1,
    'filter' => 'none',
  ],
  [
    'name' => 'YTD P/L%',
    'width' => '60px',
    'align' => 'right',
    'data' => 'profit_loss_percent',
    'formatter' => 'number_format($item["profit_loss_percent"], 2, ".", " ")',
    'aggregate' => '({YTD_PL} / ({VALUE_EUR}-{YTD_PL})) * 100', // Custom formula for aggregation.
    'sortable' => 1,
    'filter' => 'none',
  ],

  [
    'name' => '% of Selection',
    'align' => 'right',
    'data' => '{total_value} / {VALUE_EUR} * 100',
    'aggregate' => 'sum',  // Or "average" if desired.
    'sortable' => 1,
  ],
  [
    'name' => '% of Portfolio',
    'width' => '',
    'align' => 'right',
    'data' => 'percent_of_portfolio',
    'formatter' => 'number_format($item["percent_of_portfolio"], 2, ".", " ")."%"',
    'aggregate' => 'sum',  // Or "average" if desired.
    'sortable' => 1,
    'filter' => 'none',
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