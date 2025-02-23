<?php
// grid.php

// Check for required variables; you can also set defaults if needed.
if (!isset($data)) {
  die('No data provided');
}

// Check for required columns array.
if (!isset($columns) || !is_array($columns)) {
  die('Columns not defined properly.');
}

// Include the column syntax checks.
include __DIR__ . '/grid-checks.php';

if (!isset($title)) {
  $title = 'Generic view';
}

// Set the default model if not provided.
if (!isset($model)) {
  $model = 'transaction';
}

// Reindex the data array.
$data = array_values($data);

// Helper function to check if a column is hidden.
function isColumnHidden(array $col): bool
{
  return isset($col['hide']) && $col['hide'] === true;
}
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

function renderCell($item, $column)
{
  if (strpos($column['data'], '}') === false) {
    $value = $item[$column['data']];
  } else {
    $value = '?';
  }

  if (isset($column['formatter'])) {
    $value = eval ('return ' . $column['formatter'] . ';');
  }

  if (isset($column['link'])) {
    $link = $column['link'];
    foreach ($item as $key => $val) {
      $link = str_replace("{" . $key . "}", $val, $link);
    }
    $value = "<a href=\"" . $GLOBALS['BASE'] . "$link\">$value</a>";
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
  <link rel="icon" href="<?= $GLOBALS['BASE'] . '/pictures/favicon.ico' ?>" type="image/x-icon">
  <link rel="shortcut icon" href="<?= $GLOBALS['BASE'] . '/pictures/favicon.ico' ?>" type="image/x-icon">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    /* Optional: custom style for filter inputs */
    .filter-input {
      width: 100%;
      box-sizing: border-box;
    }

    a:hover {
      background-color: #f0f0f0;
    }
  </style>
</head>

<body class="bg-gray-100 flex">
  <div class="max-w-7xl w-full bg-white p-6 shadow-lg rounded-lg">

    <div class="flex items-center space-x-4">
      <img src="<?= $GLOBALS['BASE'] . '/pictures/icon-60x60.png' ?>" alt="Logo" class="h-10 w-auto">
      <h1 class="text-2xl font-semibold"><?= htmlspecialchars($title) ?></h1>
    </div>

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
              $hiddenStyle = isColumnHidden($col) ? 'display:none;' : '';
              $widthStyle = !empty($col['width']) ? 'width:' . $col['width'] . ';' : '';
              $sortable = !empty($col['sortable']);
              ?>
              <th class="border border-gray-300 px-3 py-2 text-left text-center <?= $sortable ? 'cursor-pointer' : '' ?>"
                style="<?= $widthStyle . $hiddenStyle ?>" <?= $sortable ? "onclick=\"sortTable({$colIndex})\"" : "" ?>>
                <?= $col['name'] ?>
              </th>
            <?php endforeach; ?>
          </tr>
          <!-- Header Row -->

          <!-- Filter Row -->
          <tr class="bg-gray-100">
            <?php foreach ($columns as $colIndex => $col):
              $hiddenStyle = isColumnHidden($col) ? 'display:none;' : '';
              ?>
              <td class="border border-gray-300 px-3 py-2" style="<?= $hiddenStyle ?>">
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
        <!-- Filter Row -->

        <!-- Data Rows -->
        <tbody>
          <?php foreach ($data as $item): ?>
            <tr data-row='<?= htmlspecialchars(json_encode($item), ENT_QUOTES, 'UTF-8') ?>'
              class="border border-gray-300">
              <?php foreach ($columns as $colIndex => $col):
                $hiddenStyle = isColumnHidden($col) ? 'display:none;' : '';
                $colorStyle = isset($col['color']) ? 'color:' . $col['color'] . ';' : '';
                $bgStyle = isset($col['bgcolor']) ? 'background-color:' . $col['bgcolor'] . ';' : '';
                $alignment = isset($col['align']) && $col['align'] === 'right' ? 'text-right' : 'text-left';
                if (isset($col['title'])) {
                  $titleAttr = "title=\"" . $item[$col['title']] . "\"";
                } else {
                  $titleAttr = "";
                }
                ?>
                <td class="px-3 py-2 <?= $alignment ?>" <?= $titleAttr ?>style="<?= $hiddenStyle . $colorStyle . $bgStyle ?>">
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
        <!-- Data Rows -->

        <!-- Footer Row: Aggregates -->
        <tfoot class="bg-gray-200 font-bold">
          <tr>
            <?php foreach ($columns as $colIndex => $col):
              $hiddenStyle = isColumnHidden($col) ? 'display:none;' : '';
              ?>
              <td class="border border-gray-300 px-3 py-2 text-right" style="<?= $hiddenStyle ?>">
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
        <!-- Footer Row: Aggregates -->

      </table>
    </div>
  </div>

  <!-- Modal Backdrop -->
  <div id="modalBackdrop" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden"></div>

  <!-- Modal Window -->
  <div id="modal" class="fixed inset-0 flex items-center justify-center hidden z-10">
    <div class="bg-white rounded-lg shadow-lg w-96">
      <!-- Modal Header -->
      <div class="border-b px-4 py-2 flex justify-between items-center">
        <h3 id="modalTitle" class="text-xl font-semibold">Symbol</h3>
        <button onclick="closeModal()" class="text-gray-600 hover:text-gray-800">&times;</button>
      </div>
      <!-- Modal Content -->
      <div class="p-4">
        <textarea id="notesField"
          class="w-full h-32 border border-gray-300 rounded p-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
          placeholder="Enter notes..."></textarea>
      </div>
      <!-- Modal Footer -->
      <div class="border-t px-4 py-2 flex justify-end">
        <button onclick="saveNotes()" class="bg-blue-500 text-white px-4 py-2 rounded mr-2 hover:bg-blue-600">
          Save
        </button>
        <button onclick="closeModal()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">
          Cancel
        </button>
      </div>
    </div>
  </div>

  <script>
    // Store current symbol globally
    let currentSymbol = '';

    // Opens the modal and retrieves the current notes for the given symbol
    function openModal(symbol) {
      currentSymbol = symbol;
      document.getElementById('modalTitle').textContent = symbol;

      // Show modal and backdrop
      document.getElementById('modal').classList.remove('hidden');
      document.getElementById('modalBackdrop').classList.remove('hidden');

      // Retrieve notes via AJAX GET
      fetch("<?= $GLOBALS['BASE'] ?>/symbol/getNotesAjx?symbol=" + symbol)
        .then(response => response.json())
        .then(data => {
          // Display the retrieved notes (or an empty string if not found)
          document.getElementById('notesField').value = data.notes || '';
        })
        .catch(error => {
          console.error('Error fetching notes:', error);
          document.getElementById('notesField').value = '';
        });
    }

    // Closes the modal without performing any update
    function closeModal() {
      document.getElementById('modal').classList.add('hidden');
      document.getElementById('modalBackdrop').classList.add('hidden');
    }

    // Saves the updated notes via AJAX POST and closes the modal on success
    function saveNotes() {
      const notes = document.getElementById('notesField').value;
      const formData = new FormData();
      formData.append('symbol', currentSymbol);
      formData.append('notes', notes);

      fetch("<?= $GLOBALS['BASE'] ?>/symbol/updateAjx", {
        method: 'POST',
        body: formData
      })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            closeModal();
          } else {
            console.error('Error saving notes:', data.error);
          }
        })
        .catch(error => {
          console.error('Error saving notes:', error);
        });
    }
  </script>

  <!-- JavaScript for Sorting, Filtering, and Dynamic Aggregates -->
  <script>
    // Declare a global variable to store computed aggregate values.
    var computedAggregates = {};

    // *********************************************************
    // 1. Build the Aggregates Configuration from PHP.
    // For a standard "sum" or "average" aggregate, we use it directly.
    // If the aggregate value is not "sum" or "average", it is assumed to be a formula.
    var aggregatesConfig = {};
    <?php foreach ($columns as $colIndex => $col):
      if (isset($col['aggregate'])):
        if ($col['aggregate'] === 'sum' || $col['aggregate'] === 'average'): ?>
          aggregatesConfig[<?= $colIndex ?>] = {
            type: "<?= $col['aggregate'] ?>",
            aggregateToken: "<?= addslashes($col['aggregateToken'] ?? '') ?>"
          };
        <?php else: ?>
          aggregatesConfig[<?= $colIndex ?>] = {
            type: "formula",
            formula: "<?= addslashes($col['aggregate']) ?>",
            aggregateToken: "<?= addslashes($col['aggregateToken'] ?? '') ?>"
          };
        <?php endif;
      endif;
    endforeach; ?>

    // *********************************************************
    // 2. Build the Data Formula Configuration.
    // For any column where the 'data' property contains tokens (like {colA}),
    // we assume it is a JS formula that must be evaluated for each row.
    var dataFormulaConfig = {};
    <?php foreach ($columns as $colIndex => $col):
      if (strpos($col['data'], '{') !== false && strpos($col['data'], '}') !== false): ?>
        dataFormulaConfig[<?= $colIndex ?>] = "<?= addslashes($col['data']) ?>";
      <?php endif; endforeach; ?>

    // *********************************************************
    // 3. Utility function: Evaluate a formula using a provided tokens object.
    function evaluateFormula(formula, values) {
      for (var key in values) {
        var regex = new RegExp('{' + key + '}', 'g');
        formula = formula.replace(regex, values[key]);
      }
      try {
        return eval(formula); // Use eval only with trusted inputs.
      } catch (e) {
        console.error("Error evaluating formula:", formula, e);
        return '';
      }
    }

    // *********************************************************
    // 4. Evaluate a cell's formula using the row's original data and computedAggregates.
    function evaluateCellFormula(formula, rowData) {
      var replacedFormula = formula.replace(/\{([^}]+)\}/g, function (match, token) {
        if (typeof computedAggregates !== 'undefined' && computedAggregates[token] !== undefined) {
          return computedAggregates[token];
        } else if (rowData[token] !== undefined) {
          return rowData[token];
        } else {
          console.error("Token", token, "not found in computed aggregates or row data.");
          return 0;
        }
      });
      try {
        let result = eval(replacedFormula);
        return Number.isFinite(result) ? result : 0;
      } catch (e) {
        console.error("Error evaluating cell formula:", replacedFormula, e);
        return '';
      }
    }

    // *********************************************************
    // 5. Recalculate Aggregates from the Visible Rows.
    // This computes sums or averages, and also evaluates aggregate formulas.
    function recalcAggregates() {
      var table = document.getElementById("gridView");
      var tbody = table.querySelector("tbody");
      var footerCells = table.querySelector("tfoot tr").cells;

      // Set the global computedAggregates object.
      computedAggregates = {};

      // Loop through each column configuration that is not a formula aggregate.
      for (var colIndex in aggregatesConfig) {
        var config = aggregatesConfig[colIndex];
        if (config.type !== "formula") {
          var total = 0;
          var count = 0;
          tbody.querySelectorAll("tr").forEach(function (row) {
            if (row.style.display !== "none") {
              var cell = row.cells[colIndex];
              var text = cell.innerText;
              var num = parseFloat(text.replace(/[^0-9\.\-]+/g, ""));
              if (!isNaN(num)) {
                total += num;
                count++;
              }
            }
          });
          if (config.type === "sum" && count > 0) {
            footerCells[colIndex].innerHTML =
              '<span style="text-decoration: overline; font-weight: bold;color:black;">' +
              formatNumber(total) +
              '</span>';
          }
          if (config.type === "average" && count > 0) {
            var average = (total / count).toFixed(2);
            footerCells[colIndex].innerHTML =
              '<span style="text-decoration: overline; font-weight: bold;color:grey;">avg ' + average + '%</span>';
          }
          // If an aggregateToken is provided, store the computed value for use in formulas.
          if (config.aggregateToken) {
            computedAggregates[config.aggregateToken] = total;
          }
        }
      }

      // Now, process columns that have an aggregate formula.
      for (var colIndex in aggregatesConfig) {
        var config = aggregatesConfig[colIndex];
        if (config.type === "formula" && config.formula) {
          var result = evaluateFormula(config.formula, computedAggregates);
          footerCells[colIndex].innerHTML =
            '<span style="text-decoration: overline; font-weight: bold;color:black;">' + formatNumber(result) + '</span>';
        }
      }
    }

    // *********************************************************
    // 6. Recalculate Data Cells for Columns Defined as a Formula.
    // This loops over each row, reads its original data (from data-row),
    // evaluates the column's formula using both the row data and computed aggregates,
    // and updates the cell's text.
    function formatNumber(input) {
      const num = Number(input);

      // If the conversion fails, return the original input.
      if (isNaN(num)) {
        return input;
      }

      // If the number is between -10 and 10 (non-inclusive),
      // format with exactly 2 decimal places.
      if (num < 100 && num > -100) {
        return num.toLocaleString('en-US', {
          minimumFractionDigits: 2,
          maximumFractionDigits: 2
        });
      } else {
        // For numbers >= 10 or <= -10, format with 0 decimals.
        return num
          .toLocaleString('en-US', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
          })
          .replace(/,/g, ' ');
      }
    }


    // *********************************************************
    // 7. Recalculate Data Cells for Columns Defined as a Formula.
    // This loops over each row, reads its original data (from data-row),
    // evaluates the column's formula using both the row data and computed aggregates,
    // and updates the cell's text.
    function recalcDataCells() {
      if (Object.keys(dataFormulaConfig).length === 0) return;
      var table = document.getElementById("gridView");
      var tbody = table.querySelector("tbody");
      tbody.querySelectorAll("tr").forEach(function (row) {
        var rowData = JSON.parse(row.getAttribute("data-row"));
        for (var colIndex in dataFormulaConfig) {
          var formula = dataFormulaConfig[colIndex];
          var result = evaluateCellFormula(formula, rowData);
          row.cells[colIndex].innerText = formatNumber(result);
        }
      });
    }

    // *********************************************************
    // 8. Sorting Function: Sort by the given column and then recalc.
    function sortTable(columnIndex) {
      var table = document.getElementById("gridView");
      var tbody = table.querySelector("tbody");
      var rows = Array.from(tbody.querySelectorAll("tr"));
      var currentSortCol = table.dataset.sortedCol;
      var sortDir = table.dataset.sortDir || "asc";
      if (currentSortCol == columnIndex) {
        sortDir = (sortDir === "asc") ? "desc" : "asc";
      } else {
        sortDir = "asc";
      }
      table.dataset.sortedCol = columnIndex;
      table.dataset.sortDir = sortDir;
      var sortedRows = rows.sort(function (rowA, rowB) {
        var cellA = rowA.cells[columnIndex].innerText.trim();
        var cellB = rowB.cells[columnIndex].innerText.trim();
        var numA = parseFloat(cellA.replace(/[^0-9\.\-]+/g, ""));
        var numB = parseFloat(cellB.replace(/[^0-9\.\-]+/g, ""));
        if (!isNaN(numA) && !isNaN(numB)) {
          return (sortDir === "asc") ? numA - numB : numB - numA;
        } else {
          return (sortDir === "asc") ? cellA.localeCompare(cellB) : cellB.localeCompare(cellA);
        }
      });
      tbody.innerHTML = "";
      sortedRows.forEach(function (row) {
        tbody.appendChild(row);
      });
      // First update aggregates then recalc calculated data columns.
      recalcAggregates();
      recalcDataCells();
    }

    // *********************************************************
    // 9. Filtering Function: Hide or show rows based on filter inputs.
    function filterTable() {
      var table = document.getElementById("gridView");
      var rows = table.querySelectorAll("tbody tr");
      rows.forEach(function (row) {
        var visible = true;
        document.querySelectorAll(".filter-input").forEach(function (input) {
          var colIndex = input.dataset.column;
          var filterValue = input.value.toLowerCase();
          var cellText = row.cells[colIndex].innerText.toLowerCase();
          if (filterValue && cellText.indexOf(filterValue) === -1) {
            visible = false;
          }
        });
        row.style.display = visible ? "" : "none";
      });
      recalcAggregates();
      recalcDataCells();
      recalcAggregates(); // need to do this again in order to get the corrent aggregates for calculated columns
    }

    // *********************************************************
    // 11. Attach Event Listeners to Filter Inputs.
    document.querySelectorAll(".filter-input").forEach(function (input) {
      input.addEventListener("keyup", filterTable);
      input.addEventListener("change", filterTable);
    });

    // *********************************************************
    // 12. On Initial Load, Recalculate Aggregates and then Data Cells.
    document.addEventListener("DOMContentLoaded", function () {
      filterTable();
      recalcAggregates();
      recalcDataCells();
    });
  </script>

</body>

</html>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    // Add CSS for the context menu hover effect.
    var style = document.createElement('style');
    style.innerHTML = `
    #customContextMenu li {
      cursor: pointer;
    }
    #customContextMenu li:hover {
      background-color:rgb(244, 255, 207);
    }
  `;
    document.head.appendChild(style);

    // Create the custom context menu element.
    const contextMenu = document.createElement('div');
    contextMenu.id = 'customContextMenu';
    contextMenu.style.position = 'absolute';
    contextMenu.style.background = '#fff';
    contextMenu.style.border = '1px solid #ccc';
    contextMenu.style.boxShadow = '2px 2px 5px rgba(0,0,0,0.3)';
    contextMenu.style.padding = '5px';
    contextMenu.style.display = 'none';
    contextMenu.style.zIndex = 10000;
    contextMenu.innerHTML = `
    <ul style="list-style: none; margin: 0; padding: 0;">
      <li id="menuNotes" style="padding: 8px 12px;">Notes</li>
      <li id="menuBuy" style="padding: 8px 12px;">Buy</li>
      <li id="menuSell" style="padding: 8px 12px;">Sell</li>
      <li id="menuDividend" style="padding: 8px 12px;">Dividend</li>
    </ul>
  `;
    document.body.appendChild(contextMenu);

    // Hide the custom menu when clicking anywhere else.
    document.addEventListener('click', function () {
      contextMenu.style.display = 'none';
    });

    // Find the column index for the "Symbol" column.
    let symbolColIndex = -1;
    const headerCells = document.querySelectorAll('#gridView thead tr:first-child th');
    headerCells.forEach(function (th, idx) {
      if (th.textContent.trim().toLowerCase() === 'symbol') {
        symbolColIndex = idx;
      }
    });
    if (symbolColIndex === -1) {
      // If no "Symbol" column is found, do nothing.
      return;
    }

    // Attach the contextmenu event to each cell in the symbol column.
    const rows = document.querySelectorAll('#gridView tbody tr');
    rows.forEach(function (row) {
      const cells = row.querySelectorAll('td');
      if (cells.length > symbolColIndex) {
        const symbolCell = cells[symbolColIndex];
        symbolCell.addEventListener('contextmenu', function (e) {
          e.preventDefault();  // Prevent the browser's default context menu.
          // Get the symbol from the cell text (adjust if your cell contains additional markup).
          const symbol = symbolCell.innerText.trim();

          // Set up click handlers for each option.
          document.getElementById('menuBuy').onclick = function () {
            window.location.href = '<?= $GLOBALS['BASE'] ?>/transaction/buy/' + symbol;
          };
          document.getElementById('menuSell').onclick = function () {
            window.location.href = '<?= $GLOBALS['BASE'] ?>/transaction/sell/' + symbol;
          };
          document.getElementById('menuDividend').onclick = function () {
            window.location.href = '<?= $GLOBALS['BASE'] ?>/transaction/dividend/' + symbol;
          };
          document.getElementById('menuNotes').onclick = function () {
            openModal(symbol);
            // window.location.href = '<?= $GLOBALS['BASE'] ?>/transaction/notes/' + symbol;
          };

          // Position and show the custom context menu.
          contextMenu.style.left = e.pageX + 'px';
          contextMenu.style.top = e.pageY + 'px';
          contextMenu.style.display = 'block';
        });
      }
    });
  });
</script>