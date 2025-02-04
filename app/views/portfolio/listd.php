<?php
// --- Define the grid columns ---
// Each column definition is an associative array. You can add more properties as needed.
// * name      : Header text for the column.
// * width     : Optional CSS width (e.g. "60px"). If empty nothing is output.
// * data      : A piece of PHP code to be evaluated for each row (should return the value to display).
// * aggregate : If set to "sum" or "average", the footer will display the computed total/average.
// * sortable  : (1/0) Whether this column should be sortable.
// * select    : (1/0) Whether a dropdown filter should be built for this column.
$columns = [
    [
        'name' => 'Symbol',
        'width' => '', // no width set
        'data' => 'htmlspecialchars($item["symbol"])',
        'aggregate' => null,
        'sortable' => 1,
        'select' => 1,
    ],
    [
        'name' => 'Number',
        'width' => '80px',
        'data' => 'htmlspecialchars($item["number"])',
        'aggregate' => null,
        'sortable' => 1,
        'select' => 0,
    ],
    [
        'name' => 'Average Buy Price',
        'width' => '120px',
        'data' => 'number_format($item["avg_buy_price"], 2, ".", " ")',
        'aggregate' => null,
        'sortable' => 1,
        'select' => 0,
    ],
    [
        'name' => 'Quote Date',
        'width' => '80px',
        // Display only day/month if available; otherwise a dash.
        'data' => 'isset($item["quote_date"]) && !empty($item["quote_date"]) ? date("d/m", strtotime($item["quote_date"])) : "-"',
        'aggregate' => null,
        'sortable' => 1,
        'select' => 0,
    ],
    [
        'name' => 'Latest Price',
        'width' => '100px',
        'data' => 'number_format($item["latest_price"], 2, ".", " ")',
        'aggregate' => null,
        'sortable' => 1,
        'select' => 0,
    ],
    [
        'name' => 'Total Value',
        'width' => '120px',
        'data' => 'number_format($item["total_value"], 2, ".", " ")',
        'aggregate' => 'sum',  // We want to sum up the values.
        'sortable' => 1,
        'select' => 0,
    ],
    [
        'name' => 'Profit/Loss',
        'width' => '120px',
        'data' => 'number_format($item["profit_loss"], 2, ".", " ")',
        'aggregate' => 'sum',  // Sum of profit/loss.
        'sortable' => 1,
        'select' => 0,
    ],
    [
        'name' => 'YTD Profit/Loss',
        'width' => '120px',
        'data' => 'number_format($item["ytd_profit_loss"], 2, ".", " ")',
        'aggregate' => 'sum',  // Sum of YTD profit/loss.
        'sortable' => 1,
        'select' => 0,
    ],
    [
        'name' => '% of Portfolio',
        'width' => '100px',
        'data' => 'htmlspecialchars($item["percent_of_portfolio"])."%"',
        'aggregate' => null,  // Could also be "average" if needed.
        'sortable' => 1,
        'select' => 0,
    ],
];

// --- Sample data (e.g. portfolio items) ---
$data = [
    [
        "symbol" => "AAPL",
        "number" => 10,
        "avg_buy_price" => 145.67,
        "quote_date" => "2025-01-15",
        "latest_price" => 150.12,
        "total_value" => 1501.20,
        "profit_loss" => 46.50,
        "ytd_profit_loss" => 30.20,
        "percent_of_portfolio" => 25.5,
    ],
    [
        "symbol" => "EUR",
        "number" => 5,
        "avg_buy_price" => 1.10,
        "quote_date" => "",
        "latest_price" => 1.09,
        "total_value" => 5.45,
        "profit_loss" => -0.05,
        "ytd_profit_loss" => 0.00,
        "percent_of_portfolio" => 5.0,
    ],
    [
        "symbol" => "GOOG",
        "number" => 2,
        "avg_buy_price" => 2500.00,
        "quote_date" => "2025-01-20",
        "latest_price" => 2550.00,
        "total_value" => 5100.00,
        "profit_loss" => 100.00,
        "ytd_profit_loss" => 80.00,
        "percent_of_portfolio" => 69.5,
    ],
];

// --- Prepare Aggregates ---
// For each column that has an aggregation rule, initialize its accumulator.
$aggregates = [];
foreach ($columns as $colIndex => $col) {
    if (!empty($col['aggregate'])) {
        $aggregates[$colIndex] = [
            'value' => 0,
            'count' => 0,
            'type' => $col['aggregate']  // "sum" or "average"
        ];
    }
}

// --- Precompute unique values for columns that need a select (dropdown filter) ---
$uniqueValues = [];
foreach ($columns as $colIndex => $col) {
    if (isset($col['filter']) && $col['filter'] === 'select') {
        $uniqueValues[$colIndex] = [];
        foreach ($data as $item) {
            // Evaluate the column's data code to get the cell value.
            $value = eval ('return ' . $col['data'] . ';');
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
    <title>Generic Grid View</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Optional: some custom styling for filter inputs */
        .filter-input {
            width: 100%;
            box-sizing: border-box;
        }
    </style>
</head>

<body class="bg-gray-100 flex justify-center items-center min-h-screen">
    <div class="max-w-7xl w-full bg-white p-6 shadow-lg rounded-lg">
        <h1 class="text-2xl font-semibold mb-4">Generic Grid View</h1>
        <!-- The grid table -->
        <div class="overflow-x-auto">
            <table id="gridView" class="w-full border-collapse border border-gray-300" data-sorted-col="">
                <thead class="bg-gray-200">
                    <!-- Header Row -->
                    <tr>
                        <?php foreach ($columns as $colIndex => $col):
                            // Add a style if width is defined.
                            $widthStyle = !empty($col['width']) ? " style=\"width:{$col['width']}\"" : "";
                            // If the column is sortable, add a cursor style and an onclick handler.
                            $sortable = !empty($col['sortable']);
                            ?>
                            <th class="border border-gray-300 px-3 py-2 text-left <?= $sortable ? 'cursor-pointer' : '' ?>"
                                <?= $widthStyle ?>     <?= $sortable ? "onclick=\"sortTable({$colIndex})\"" : "" ?>>
                                <?= htmlspecialchars($col['name']) ?>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                    <!-- Filter Row -->
                    <tr class="bg-gray-100">
                        <?php foreach ($columns as $colIndex => $col): ?>
                            <td class="border border-gray-300 px-3 py-2">
                                <?php if (!empty($col['select'])):
                                    // Build a select dropdown from the precomputed unique values.
                                    ?>
                                    <select class="filter-input" data-column="<?= $colIndex ?>">
                                        <option value="">All</option>
                                        <?php foreach ($uniqueValues[$colIndex] as $value): ?>
                                            <option value="<?= htmlspecialchars($value) ?>"><?= htmlspecialchars($value) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php else: ?>
                                    <input type="text" class="filter-input" data-column="<?= $colIndex ?>"
                                        placeholder="Filter <?= htmlspecialchars($col['name']) ?>">
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data as $item): ?>
                        <tr class="border border-gray-300">
                            <?php foreach ($columns as $colIndex => $col): ?>
                                <td class="px-3 py-2 <?= in_array($colIndex, [5, 6, 7, 8]) ? 'text-right' : 'text-left' ?>">
                                    <?php
                                    // Evaluate the column's code snippet in the context of the current row ($item).
                                    // Using eval() here; in real applications consider safer alternatives.
                                    $cellValue = eval ('return ' . $col['data'] . ';');
                                    echo $cellValue;

                                    // If this column has an aggregation rule, update our accumulator.
                                    if (isset($aggregates[$colIndex])) {
                                        // Remove formatting (if any) so that we can add numerically.
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
                                        // Default is sum.
                                        echo number_format($agg['value'], 2, '.', ' ');
                                    }
                                } else {
                                    // For columns without aggregation, you can output an empty cell or a label (e.g. "Total")
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
        // Sorting Function (improved with explicit sort direction)
        function sortTable(columnIndex) {
            const table = document.getElementById("gridView");
            const tbody = table.querySelector("tbody");
            const rows = Array.from(tbody.querySelectorAll("tr"));

            // Get current sort column and direction; default to ascending if not set.
            let currentSortCol = table.dataset.sortedCol;
            let sortDir = table.dataset.sortDir || "asc";

            // If the same column is clicked, toggle the sort direction.
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

                // Try number comparison first.
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

            // If descending, reverse the sorted order.
            if (sortDir === "desc") {
                sortedRows.reverse();
            }

            // Replace tbody rows with the sorted rows.
            tbody.innerHTML = "";
            sortedRows.forEach(row => tbody.appendChild(row));
        }
    </script>

</body>

</html>