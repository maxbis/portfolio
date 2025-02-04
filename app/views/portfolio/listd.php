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
        'name'      => 'Symbol',
        'width'     => '80px',
        'data'      => 'htmlspecialchars($item["symbol"])',
        'aggregate' => null,
        'sortable'  => 1,
        'filter'    => 'select',  // Dropdown filter.
    ],
    [
        'name'      => 'Number',
        'width'     => '80px',
        'data'      => 'htmlspecialchars($item["number"])',
        'aggregate' => null,
        'sortable'  => 1,
        'filter'    => 'text',  // Text input filter.
    ],
    [
        'name'      => 'Avg Price',
        'width'     => '80px',
        'data'      => 'number_format($item["avg_buy_price"], 2, ".", " ")',
        'aggregate' => null,
        'sortable'  => 1,
        'filter'    => 'text',
    ],
    [
        'name'      => 'QDate',
        'width'     => '80px',
        'data'      => 'isset($item["quote_date"]) && !empty($item["quote_date"]) ? date("d/m", strtotime($item["quote_date"])) : "-"',
        'aggregate' => null,
        'sortable'  => 1,
        'filter'    => 'text',
    ],
    [
        'name'      => 'Quote',
        'width'     => '80px',
        'data'      => 'number_format($item["latest_price"], 2, ".", " ")',
        'aggregate' => null,
        'sortable'  => 1,
        'filter'    => 'none',  // No filter.
    ],
    [
        'name'      => 'Value',
        'width'     => '100px',
        'data'      => 'number_format($item["total_value"], 2, ".", " ")',
        'aggregate' => 'sum',  // Sum the values.
        'sortable'  => 1,
        'filter'    => 'none',
    ],
    [
        'name'      => 'Profit/Loss',
        'width'     => '100px',
        'data'      => 'number_format($item["profit_loss"], 2, ".", " ")',
        'aggregate' => 'sum',  // Sum profit/loss.
        'sortable'  => 1,
        'filter'    => 'none',
    ],
    [
        'name'      => 'YTD P/L',
        
        'width'     => '100px',
        'data'      => 'number_format($item["ytd_profit_loss"], 2, ".", " ")',
        'aggregate' => 'sum',  // Sum YTD profit/loss.
        'sortable'  => 1,
        'filter'    => 'none',
    ],
    [
        'name'      => '% of Portfolio',
        'width'     => '',
        'data'      => 'htmlspecialchars($item["percent_of_portfolio"])."%"',
        'aggregate' => null,  // Or "average" if desired.
        'sortable'  => 1,
        'filter'    => 'text',
    ],
];

// --- Sample data (for example, portfolio items) ---
$_data = [
    [
        "symbol"            => "AAPL",
        "number"            => 10,
        "avg_buy_price"     => 145.67,
        "quote_date"        => "2025-01-15",
        "latest_price"      => 150.12,
        "total_value"       => 1501.20,
        "profit_loss"       => 46.50,
        "ytd_profit_loss"   => 30.20,
        "percent_of_portfolio" => 25.5,
    ],
    [
        "symbol"            => "EUR",
        "number"            => 5,
        "avg_buy_price"     => 1.10,
        "quote_date"        => "",
        "latest_price"      => 1.09,
        "total_value"       => 5.45,
        "profit_loss"       => -0.05,
        "ytd_profit_loss"   => 0.00,
        "percent_of_portfolio" => 5.0,
    ],
    [
        "symbol"            => "GOOG",
        "number"            => 2,
        "avg_buy_price"     => 2500.00,
        "quote_date"        => "2025-01-20",
        "latest_price"      => 2550.00,
        "total_value"       => 5100.00,
        "profit_loss"       => 100.00,
        "ytd_profit_loss"   => 80.00,
        "percent_of_portfolio" => 69.5,
    ],
];

if (!isset($title)) $title = 'Generic view (specify titel in controller)';

// --- Prepare Aggregates ---
// Initialize accumulators for columns with aggregation rules.
$aggregates = [];
foreach ($columns as $colIndex => $col) {
    if (!empty($col['aggregate'])) {
        $aggregates[$colIndex] = [
            'value' => 0,
            'count' => 0,
            'type'  => $col['aggregate']  // "sum" or "average"
        ];
    }
}

// --- Precompute unique values for dropdown filters ---
// Loop only for columns where filter type is 'select'
$uniqueValues = [];
foreach ($columns as $colIndex => $col) {
    if (isset($col['filter']) && $col['filter'] === 'select') {
        $uniqueValues[$colIndex] = [];
        foreach ($data as $item) {
            // Evaluate the column's data code.
            $value = eval('return ' . $col['data'] . ';');
            $uniqueValues[$colIndex][$value] = $value;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= $title ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    /* Optional: custom style for filter inputs */
    .filter-input { width: 100%; box-sizing: border-box; }
  </style>
</head>
<body class="bg-gray-100 flex justify-center items-center min-h-screen">
  <div class="max-w-7xl w-full bg-white p-6 shadow-lg rounded-lg">
    <h1 class="text-2xl font-semibold mb-4"><?= $title ?></h1>
    <!-- The grid table -->
    <div class="overflow-x-auto">
      <table id="gridView" class="w-full border-collapse border border-gray-300" data-sorted-col="" data-sort-dir="asc">
        <thead class="bg-gray-200">
          <!-- Header Row -->
          <tr>
            <?php foreach ($columns as $colIndex => $col): 
              $widthStyle = !empty($col['width']) ? " style=\"width:{$col['width']}\"" : "";
              $sortable = !empty($col['sortable']);
              ?>
              <th class="border border-gray-300 px-3 py-2 text-left  text-center <?= $sortable ? 'cursor-pointer' : '' ?>" 
                  <?= $widthStyle ?>
                  <?= $sortable ? "onclick=\"sortTable({$colIndex})\"" : "" ?>>
                <?= htmlspecialchars($col['name']) ?>
              </th>
            <?php endforeach; ?>
          </tr>
          <!-- Filter Row -->
          <tr class="bg-gray-100">
            <?php foreach ($columns as $colIndex => $col): ?>
              <td class="border border-gray-300 px-3 py-2">
                <?php if (isset($col['filter'])): 
                  if ($col['filter'] === 'select') { ?>
                    <select class="filter-input" data-column="<?= $colIndex ?>">
                      <option value="">All</option>
                      <?php foreach ($uniqueValues[$colIndex] as $value): ?>
                        <option value="<?= htmlspecialchars($value) ?>"><?= htmlspecialchars($value) ?></option>
                      <?php endforeach; ?>
                    </select>
                  <?php } elseif ($col['filter'] === 'text') { ?>
                    <input type="text" class="filter-input" data-column="<?= $colIndex ?>" placeholder="Filter <?= htmlspecialchars($col['name']) ?>">
                  <?php } 
                  // If filter is 'none', output nothing.
                endif; ?>
              </td>
            <?php endforeach; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($data as $item): ?>
            <tr class="border border-gray-300">
              <?php foreach ($columns as $colIndex => $col): ?>
                <td class="px-3 py-2 <?= ($colIndex >= 5) ? 'text-right' : 'text-left' ?>">
                  <?php
                  // Evaluate the code snippet to get the cell value.
                  $cellValue = eval('return ' . $col['data'] . ';');
                  echo $cellValue;
                  
                  // If aggregation is required, update the accumulator.
                  if (isset($aggregates[$colIndex])) {
                      // Remove formatting to allow numeric addition.
                      $numeric = floatval(str_replace([',', ' '], '', $cellValue));
                      $aggregates[$colIndex]['value'] += $numeric;
                      $aggregates[$colIndex]['count']++;
                  }
                  ?>
                </td>
              <?php endforeach; ?>
            </tr>
          <?php endforeach; ?>
        </tbody>
        <!-- Footer Row: Aggregates -->
        <tfoot class="bg-gray-200 font-bold">
          <tr>
            <?php foreach ($columns as $colIndex => $col): ?>
              <td class="border border-gray-300 px-3 py-2 text-right">
                <?php
                if (isset($aggregates[$colIndex])) {
                    $agg = $aggregates[$colIndex];
                    if ($agg['type'] === 'average' && $agg['count'] > 0) {
                        echo number_format($agg['value'] / $agg['count'], 2, '.', ' ');
                    } else {
                        echo number_format($agg['value'], 2, '.', ' ');
                    }
                } else {
                    // For the first column, you might label it as "Total"
                    echo ($colIndex === 0) ? "Total" : "";
                }
                ?>
              </td>
            <?php endforeach; ?>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>

  <!-- JavaScript for Sorting and Filtering -->
  <script>
    // Sorting Function (with explicit sort direction tracking)
    function sortTable(columnIndex) {
      const table = document.getElementById("gridView");
      const tbody = table.querySelector("tbody");
      const rows = Array.from(tbody.querySelectorAll("tr"));

      // Get current sorted column and sort direction
      let currentSortCol = table.dataset.sortedCol;
      let sortDir = table.dataset.sortDir || "asc";

      // Toggle sort direction if the same column is clicked, otherwise reset to ascending.
      if (currentSortCol == columnIndex) {
        sortDir = (sortDir === "asc") ? "desc" : "asc";
      } else {
        sortDir = "asc";
      }
      table.dataset.sortedCol = columnIndex;
      table.dataset.sortDir = sortDir;

      const sortedRows = rows.sort((rowA, rowB) => {
        const cellA = rowA.cells[columnIndex].innerText.trim();
        const cellB = rowB.cells[columnIndex].innerText.trim();

        // Attempt numeric comparison first.
        const numA = parseFloat(cellA.replace(/[^0-9.-]+/g, ""));
        const numB = parseFloat(cellB.replace(/[^0-9.-]+/g, ""));
        
        let comparison = 0;
        if (!isNaN(numA) && !isNaN(numB)) {
          comparison = numA - numB;
        } else {
          comparison = cellA.localeCompare(cellB);
        }
        return comparison;
      });

      // Reverse rows if descending sort.
      if (sortDir === "desc") {
        sortedRows.reverse();
      }

      // Replace tbody rows with sorted rows.
      tbody.innerHTML = "";
      sortedRows.forEach(row => tbody.appendChild(row));
    }

    // Filtering Function
    document.querySelectorAll(".filter-input").forEach(input => {
      input.addEventListener("keyup", filterTable);
      input.addEventListener("change", filterTable); // for select elements
    });

    function filterTable() {
      const table = document.getElementById("gridView");
      const rows = table.querySelectorAll("tbody tr");

      // For each row, check every filter input.
      rows.forEach(row => {
        let visible = true;
        document.querySelectorAll(".filter-input").forEach(input => {
          const colIndex = input.dataset.column;
          const filterValue = input.value.toLowerCase();
          const cellText = row.cells[colIndex].innerText.toLowerCase();
          if (filterValue && cellText.indexOf(filterValue) === -1) {
            visible = false;
          }
        });
        row.style.display = visible ? "" : "none";
      });
    }
  </script>
</body>
</html>
