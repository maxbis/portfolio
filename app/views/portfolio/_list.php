<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Portfolio Overview</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex justify-center items-center min-h-screen">
  <div class="max-w-5xl w-full bg-white p-6 shadow-lg rounded-lg">
    <h1 class="text-2xl font-semibold mb-4">Portfolio Overview</h1>

    <?php $activeTab = 'portfolio'; include_once __DIR__ . "/../common/nav.php"; ?>

    <!-- Responsive Table Container -->
    <div class="overflow-x-auto">
      <table id="portfolioTable" class="w-full border-collapse border border-gray-300">
        <!-- Table Header with Sortable Columns -->
        <thead class="bg-gray-200">
          <tr>
            <th class="border border-gray-300 px-3 py-2 text-left cursor-pointer" onclick="sortTable(0)">Symbol</th>
            <th class="border border-gray-300 px-3 py-2 text-left cursor-pointer" onclick="sortTable(1)">Number</th>
            <th class="border border-gray-300 px-3 py-2 text-left cursor-pointer" onclick="sortTable(2)">Average Buy Price</th>
            <th class="border border-gray-300 px-3 py-2 text-left cursor-pointer" onclick="sortTable(3)">Quote Date</th>
            <th class="border border-gray-300 px-3 py-2 text-left cursor-pointer" onclick="sortTable(3)">Quote</th>
            <th class="border border-gray-300 px-3 py-2 text-right cursor-pointer" onclick="sortTable(4)">Total Value</th>
            <th class="border border-gray-300 px-3 py-2 text-right cursor-pointer" onclick="sortTable(5)">Profit/Loss</th>
            <th class="border border-gray-300 px-3 py-2 text-right cursor-pointer" onclick="sortTable(6)">YTD Profit/Loss</th>
            <th class="border border-gray-300 px-3 py-2 text-right cursor-pointer" onclick="sortTable(7)">% of Portfolio</th>
          </tr>
          <!-- Filter Input Fields -->
          <tr class="bg-gray-100">
            <td class="border border-gray-300 px-3 py-2">
              <input type="text" class="filter-input w-full" data-column="0" placeholder="Filter Symbol">
            </td>
            <td class="border border-gray-300 px-3 py-2">
              <input type="text" class="filter-input w-full" data-column="1" placeholder="Filter Number">
            </td>
            <td class="border border-gray-300 px-3 py-2">
              <input type="text" class="filter-input w-full" data-column="2" placeholder="Filter Avg. Price">
            </td>
            <td class="border border-gray-300 px-3 py-2">
              <input type="text" class="filter-input w-full" data-column="3" placeholder="Filter Quote Date">
            </td>
            <td class="border border-gray-300 px-3 py-2">
              <input type="text" class="filter-input w-full" data-column="3" placeholder="Filter Latest Price">
            </td>
            <td class="border border-gray-300 px-3 py-2">
              <input type="text" class="filter-input w-full" data-column="4" placeholder="Filter Total Value">
            </td>
            <td class="border border-gray-300 px-3 py-2">
              <input type="text" class="filter-input w-full" data-column="5" placeholder="Filter Profit/Loss">
            </td>
            <td class="border border-gray-300 px-3 py-2">
              <input type="text" class="filter-input w-full" data-column="6" placeholder="Filter YTD Profit/Loss">
            </td>
            <td class="border border-gray-300 px-3 py-2">
              <input type="text" class="filter-input w-full" data-column="7" placeholder="Filter %">
            </td>
          </tr>
        </thead>

        <!-- Table Body -->
        <tbody>
          <?php 
          $grandTotalValue    = 0;
          $grandProfitLoss    = 0;
          $grandYTDProfitLoss = 0;
          foreach ($portfolio as $item): 
              $grandTotalValue    += $item['total_value'];
              $grandProfitLoss    += $item['profit_loss'];
              $grandYTDProfitLoss += $item['ytd_profit_loss'];
          ?>
          <tr class="border border-gray-300 <?= (isset($item['symbol']) && strtoupper($item['symbol']) === 'EUR') ? 'bg-green-100' : '' ?>">
            <td class="px-3 py-2"><?= htmlspecialchars($item['symbol']) ?></td>
            <td class="px-3 py-2"><?= htmlspecialchars($item['number']) ?></td>
            <td class="px-3 py-2"><?= $item['avg_buy_price'] ?></td>
            <td class="px-3 py-2">
              <?php 
                // Display only day and month if quote_date is available; otherwise, show a dash.
                if (isset($item['quote_date']) && !empty($item['quote_date'])) {
                  echo date("d/m", strtotime($item['quote_date']));
                } else {
                  echo "-";
                }
              ?>
            </td>
            <td class="px-3 py-2"><?= $item['latest_price'] ?></td>
            <td class="px-3 py-2 text-right"><?= number_format($item['total_value'], 2, '.', ' ') ?></td>
            <td class="px-3 py-2 text-right"><?= number_format($item['profit_loss'], 2, '.', ' ') ?></td>
            <td class="px-3 py-2 text-right"><?= number_format($item['ytd_profit_loss'], 2, '.', ' ') ?></td>
            <td class="px-3 py-2 text-right"><?= htmlspecialchars($item['percent_of_portfolio']) ?>%</td>
          </tr>
          <?php endforeach; ?>
        </tbody>

        <!-- Table Footer: Total Line -->
        <tfoot class="bg-gray-200 font-bold">
          <tr>
            <td class="border border-gray-300 px-3 py-2">Total</td>
            <!-- Leave empty for Symbol, Number, Avg. Price and Quote Date -->
            <td class="border border-gray-300 px-3 py-2"></td>
            <td class="border border-gray-300 px-3 py-2"></td>
            <td class="border border-gray-300 px-3 py-2"></td>
            <td class="border border-gray-300 px-3 py-2"></td>
            <td class="border border-gray-300 px-3 py-2 text-right"><?= number_format($grandTotalValue, 2, '.', ' ') ?></td>
            <td class="border border-gray-300 px-3 py-2 text-right"><?= number_format($grandProfitLoss, 2, '.', ' ') ?></td>
            <td class="border border-gray-300 px-3 py-2 text-right"><?= number_format($grandYTDProfitLoss, 2, '.', ' ') ?></td>
            <td class="border border-gray-300 px-3 py-2 text-right">100%</td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>

  <!-- JavaScript for Sorting and Filtering -->
  <script>
    // Sorting Function
    function sortTable(columnIndex) {
      const table = document.getElementById("portfolioTable");
      const rows = Array.from(table.querySelectorAll("tbody tr"));
      const sortedRows = rows.sort((rowA, rowB) => {
        const cellA = rowA.cells[columnIndex].innerText.trim();
        const cellB = rowB.cells[columnIndex].innerText.trim();
        // For numbers, remove non-numeric characters (like % or currency symbols)
        const numA = parseFloat(cellA.replace(/[^0-9.-]+/g, ""));
        const numB = parseFloat(cellB.replace(/[^0-9.-]+/g, ""));
        if (!isNaN(numA) && !isNaN(numB)) {
          return numA - numB;
        }
        return cellA.localeCompare(cellB);
      });

      if (table.dataset.sortedCol == columnIndex) {
        sortedRows.reverse(); // Toggle sort order
        table.dataset.sortedCol = "";
      } else {
        table.dataset.sortedCol = columnIndex;
      }

      const tbody = table.querySelector("tbody");
      tbody.innerHTML = "";
      sortedRows.forEach(row => tbody.appendChild(row));
    }

    // Filtering Function
    document.querySelectorAll(".filter-input").forEach(input => {
      input.addEventListener("keyup", function () {
        const columnIndex = this.dataset.column;
        const filterValue = this.value.toLowerCase();
        const table = document.getElementById("portfolioTable");
        const rows = table.querySelectorAll("tbody tr");
        rows.forEach(row => {
          const cellText = row.cells[columnIndex].innerText.toLowerCase();
          row.style.display = cellText.includes(filterValue) ? "" : "none";
        });
      });
    });
  </script>
</body>
</html>
