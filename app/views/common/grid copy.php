<?php
// grid.php


// Check for required variables; you can also set defaults if needed.
if (!isset($data)) {
  die('No data provided');
}

if (!isset($columns) || !is_array($columns)) {
  die('Columns not defined properly.');
}

if (!isset($title)) {
  $title = 'Generic view';
}

if (!isset($model)) {
  $model = 'transaction';
}

// Reindex the data array.
$data = array_values($data);
?>

<?php if (empty($data)): ?>
  <script src="https://cdn.tailwindcss.com"></script>
  <div class="container mx-auto mt-4">
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
      <strong class="font-bold">Warning!</strong>
      <span class="block sm:inline">No data to display, empty table?</span>
    </div>
    <br>
    <div class="mb-4">
      <a href="<?= htmlspecialchars($GLOBALS['BASE'] . '/' . $model . '/create') ?>"
        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
        New
      </a>
    </div>
  </div>
  <?php exit; endif; ?>

<?php
// Check if all defined columns exist.
foreach ($columns as $col) {
  if (substr($col['data'], 0, 1) !== '#' && !array_key_exists($col['data'], $data[0])) {
    echo "<pre>";
    echo "File: " . __FILE__ . ", Line: " . __LINE__ . PHP_EOL;
    echo "Error: Key '{$col['data']}' does not exist in the item array.";
    echo '$data: ' . PHP_EOL;
    print_r($data);
    echo '$columns: ' . PHP_EOL;
    print_r($columns);
    exit("Error: Key '{$col['data']}' does not exist in the item array.");
  }
}

// --- Prepare Aggregates ---
// These accumulators are here in case no filtering happens,
// but the footer will be updated dynamically.
$aggregates = [];
foreach ($columns as $colIndex => $col) {
  if (!empty($col['aggregate'])) {
    $aggregates[$colIndex] = [
      'value' => 0,
      'count' => 0,
      'type' => $col['aggregate']  // "sum", "average", or "formula"
    ];
  }
}

// --- Precompute unique values for dropdown filters ---
$uniqueValues = [];
foreach ($columns as $colIndex => $col) {
  if (isset($col['filter']) && $col['filter'] === 'select') {
    $uniqueValues[$colIndex] = [];
    foreach ($data as $item) {
      $value = $item[$col['data']];
      $uniqueValues[$colIndex][$value] = $value;
    }
  }
}

function renderCell($item, $column) {
    $value = $item[$column['data']];
    if (isset($column['formatter'])) {
        $value = eval('return ' . $column['formatter'] . ';');
    }

    if (isset($column['link'])) {
        $link = $column['link'];
        foreach ($item as $key => $val) {
            $link = str_replace("{" . $key . "}", $val, $link);
        }
        $value = "<a href=\"".$GLOBALS['BASE']."$link\">$value</a>";
    }

    return $value;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars($title) ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    /* Optional: custom style for filter inputs */
    .filter-input {
      width: 100%;
      box-sizing: border-box;
    }
  </style>
</head>

<body class="bg-gray-100 flex">
  <div class="max-w-7xl w-full bg-white p-6 shadow-lg rounded-lg">
    <h1 class="text-2xl font-semibold mb-4"><?= htmlspecialchars($title) ?></h1>
    <?php include_once __DIR__ . "/../common/nav.php"; ?>

    <?php if (!isset($noCreate)): ?>
      <div class="mb-4">
        <a href="<?= $GLOBALS['BASE'] . '/' . $model ?>/create"
          class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
          New
        </a>
      </div>
    <?php endif; ?>

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
              <th class="border border-gray-300 px-3 py-2 text-left text-center <?= $sortable ? 'cursor-pointer' : '' ?>"
                <?= $widthStyle ?>   <?= $sortable ? "onclick=\"sortTable({$colIndex})\"" : "" ?>>
                <?= $col['name'] ?>
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
                        <option value="<?= htmlspecialchars($value) ?>" <?= (isset($_GET[$col['data']]) && $_GET[$col['data']] == $value) ? 'selected' : '' ?>>
                          <?= htmlspecialchars($value) ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  <?php } elseif ($col['filter'] === 'text') { ?>
                    <input type="text" class="filter-input" data-column="<?= $colIndex ?>"
                      placeholder="Filter <?= htmlspecialchars($col['name']) ?>"
                      value="<?= isset($_GET[$col['data']]) ? htmlspecialchars($_GET[$col['data']]) : '' ?>">
                  <?php }
                endif; ?>
              </td>
            <?php endforeach; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($data as $item): ?>
            <tr class="border border-gray-300">
              <?php foreach ($columns as $colIndex => $col):
                $alignment = isset($col['align']) && $col['align'] === 'right' ? 'text-right' : 'text-left';
                if (isset($col['title'])) {
                  $titleAttr = "title=\"" . $item[$col['title']] . "\"";
                } else {
                  $titleAttr = "";
                }
                ?>
                <td class="px-3 py-2 <?= $alignment ?>" <?= $titleAttr ?>>
                  <?php
                  if ($col['data'] === '#edit') {
                    $cellValue = sprintf(
                      '<a href="%s/%s/edit/%s" class="hover:bg-yellow-500 text-black p-2 rounded-lg transition duration-200 transform hover:scale-110">✏️</a>',
                      $GLOBALS['BASE'],
                      $model,
                      $item['id']
                    );
                  } else {
                    // If a formatter is defined, use it.
                    if (isset($col['formatter'])) {
                      $cellValue = eval ('return ' . $col['formatter'] . ';');
                    } else {
                      $cellValue = renderCell($item, $col);
                    }
                  }
                  echo $cellValue;

                  // Update PHP accumulator (in case no filtering happens)
                  if (isset($aggregates[$colIndex])) {
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
                  // This value will be updated dynamically by JS.
                  echo '<span style="text-decoration: overline; font-weight: bold;color:black;"></span>';
                }
                ?>
              </td>
            <?php endforeach; ?>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>

  <!-- JavaScript for Sorting, Filtering, and Dynamic Aggregates -->
  <script>
    // *********************************************************
    // 1. Pass aggregation configuration from PHP to JS
    // *********************************************************
    var aggregatesConfig = {};
    <?php foreach ($columns as $colIndex => $col):
      if (isset($col['aggregate'])):
        if ($col['aggregate'] === 'formula' && isset($col['formula'])): ?>
          aggregatesConfig[<?= $colIndex ?>] = {
            type: "formula",
            formula: "<?= addslashes($col['formula']) ?>",
            aggregateToken: "<?= addslashes($col['aggregateToken'] ?? '') ?>"
          };
        <?php else: ?>
          aggregatesConfig[<?= $colIndex ?>] = {
            type: "<?= $col['aggregate'] ?>",
            aggregateToken: "<?= addslashes($col['aggregateToken'] ?? '') ?>"
          };
          <?php
        endif;
      endif;
    endforeach; ?>

    // *********************************************************
    // 2. Function to safely evaluate a formula with tokens replaced by computed aggregates
    // *********************************************************
    function evaluateFormula(formula, values) {
      for (var key in values) {
        var regex = new RegExp('{' + key + '}', 'g');
        formula = formula.replace(regex, values[key]);
      }
      try {
        return eval(formula); // Use eval only with trusted inputs.
      } catch (e) {
        console.error("Error evaluating formula:", e);
        return '';
      }
    }

    // *********************************************************
    // 3. Function to recalc aggregates based on only visible rows
    // *********************************************************
    function recalcAggregates() {
      var table = document.getElementById("gridView");
      var tbody = table.querySelector("tbody");
      var footerCells = table.querySelector("tfoot tr").cells;

      // Object to hold computed aggregate values keyed by aggregateToken.
      var computedAggregates = {};

      // Loop through each column configuration that is not a formula.
      for (var colIndex in aggregatesConfig) {
        var config = aggregatesConfig[colIndex];
        if (config.type !== "formula") {
          var total = 0;
          var count = 0;
          tbody.querySelectorAll("tr").forEach(function (row) {
            if (row.style.display !== "none") {
              var cell = row.cells[colIndex];
              var text = cell.innerText;
              var num = parseFloat(text.replace(/[^0-9.-]+/g, ""));
              if (!isNaN(num)) {
                total += num;
                count++;
              }
            }
          });
          // Update footer cell based on the type of aggregate.
          if (config.type === "sum" && count > 0) {
            footerCells[colIndex].innerHTML = '<span style="text-decoration: overline; font-weight: bold;color:black;">' +
              total.toLocaleString('fr-FR', { minimumFractionDigits: 0, maximumFractionDigits: 0 }) +
              '</span>';
          }
          if (config.type === "average" && count > 0) {
            var average = (total / count).toFixed(2);
            footerCells[colIndex].innerHTML = '<span style="text-decoration: overline; font-weight: bold;color:grey;">avg ' + average + '%</span>';
          }
          // If an aggregateToken is provided, store the computed aggregate value.
          if (config.aggregateToken) {
            computedAggregates[config.aggregateToken] = total; // Adjust this if you need averages instead.
          }
        }
      }

      // Now, process columns with type "formula".
      for (var colIndex in aggregatesConfig) {
        var config = aggregatesConfig[colIndex];
        if (config.type === "formula" && config.formula) {
          var result = evaluateFormula(config.formula, computedAggregates);
          var formatted = (typeof result === "number") ? result.toFixed(2) : result;
          footerCells[colIndex].innerHTML = '<span style="text-decoration: overline; font-weight: bold;color:black;">' + formatted + '</span>';
        }
      }

      // Calculate and update the percentage of total value for each row.
      var totalValue = computedAggregates['VALUE_EUR'] || 0;
      tbody.querySelectorAll("tr").forEach(function (row) {
        var totalValueCell = row.cells[<?= array_search('total_value', array_column($columns, 'data')) ?>];
        var percentCell = row.cells[<?= array_search('percent_of_total_value', array_column($columns, 'data')) ?>];
        var totalValueCell = parseFloat(totalValueCell.innerText.replace(/[^0-9.-]+/g, ""));
        var percent = (totalValueCell / totalValue) * 100;
        percentCell.innerText = percent.toFixed(2) + "%";
      });
    }

    // *********************************************************
    // 4. Sorting Function (modified to call recalcAggregates)
    // *********************************************************
    function sortTable(columnIndex) {
      const table = document.getElementById("gridView");
      const tbody = table.querySelector("tbody");
      const rows = Array.from(tbody.querySelectorAll("tr"));
      let currentSortCol = table.dataset.sortedCol;
      let sortDir = table.dataset.sortDir || "asc";
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
      if (sortDir === "desc") {
        sortedRows.reverse();
      }
      tbody.innerHTML = "";
      sortedRows.forEach(row => tbody.appendChild(row));
      recalcAggregates();
    }

    // *********************************************************
    // 5. Filtering Function (modified to call recalcAggregates)
    // *********************************************************
    function filterTable() {
      const table = document.getElementById("gridView");
      const rows = table.querySelectorAll("tbody tr");
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
      recalcAggregates();
    }

    // Attach event listeners for filter inputs.
    document.querySelectorAll(".filter-input").forEach(input => {
      input.addEventListener("keyup", filterTable);
      input.addEventListener("change", filterTable);
    });

    // Recalculate aggregates on initial load.
    document.addEventListener("DOMContentLoaded", function () {
      // Apply filters based on pre-populated values from GET parameters
      filterTable();
      recalcAggregates();
    });
  </script>
</body>

</html>