<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction List</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 flex justify-center items-center min-h-screen">
    <div class="max-w-5xl w-full bg-white p-6 shadow-lg rounded-lg">
        <h1 class="text-2xl font-semibold mb-4">Transaction List</h1>

        <!-- Add Transaction Button -->
        <div class="mb-4">
            <a href="<?= $GLOBALS['BASE'] ?>/transaction/create"
                class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
                + Add New Transaction
            </a>
        </div>

        <?php  $activeTab = 'transaction'; include_once __DIR__ . "/../common/nav.php"; ?>

        <!-- Responsive Table Container -->
        <div class="overflow-x-auto">
            <table id="transactionTable" class="w-full border-collapse border border-gray-300">
                <!-- Table Header with Sortable Columns -->
                <thead class="bg-gray-200">
                    <tr>
                        <th class="border border-gray-300 px-3 py-2 text-left cursor-pointer" onclick="sortTable(0)">ID
                        </th>
                        <th class="border border-gray-300 px-3 py-2 text-left cursor-pointer" onclick="sortTable(1)">
                            Timestamp</th>
                        <th class="border border-gray-300 px-3 py-2 text-left cursor-pointer" onclick="sortTable(2)">
                            Amount</th>
                        <th class="border border-gray-300 px-3 py-2 text-left cursor-pointer" onclick="sortTable(3)">
                            Number</th>
                        <th class="border border-gray-300 px-3 py-2 text-left cursor-pointer" onclick="sortTable(4)">
                            Symbol</th>
                        <th class="border border-gray-300 px-3 py-2 text-left cursor-pointer" onclick="sortTable(5)">
                            Exchange</th>
                        <th class="border border-gray-300 px-3 py-2 text-left cursor-pointer" onclick="sortTable(6)">
                            Description</th>
                        <th class="border border-gray-300 px-3 py-2 text-left">Actions</th>
                    </tr>

                    <!-- Filter Input Fields -->
                    <tr class="bg-gray-100">
                        <td class="border border-gray-300 px-3 py-2"><input type="text" class="filter-input w-full"
                                data-column="0" placeholder="Filter ID"></td>
                        <td class="border border-gray-300 px-3 py-2"><input type="text" class="filter-input w-full"
                                data-column="1" placeholder="Filter Date"></td>
                        <td class="border border-gray-300 px-3 py-2"><input type="text" class="filter-input w-full"
                                data-column="2" placeholder="Filter Amount"></td>
                        <td class="border border-gray-300 px-3 py-2"><input type="text" class="filter-input w-full"
                                data-column="3" placeholder="Filter Number"></td>
                        <td class="border border-gray-300 px-3 py-2"><input type="text" class="filter-input w-full"
                                data-column="4" placeholder="Filter Symbol"></td>
                        <td class="border border-gray-300 px-3 py-2"><input type="text" class="filter-input w-full"
                                data-column="5" placeholder="Filter Exchange"></td>
                        <td class="border border-gray-300 px-3 py-2"><input type="text" class="filter-input w-full"
                                data-column="6" placeholder="Filter Description"></td>
                        <td class="border border-gray-300 px-3 py-2"></td>
                    </tr>
                </thead>

                <!-- Table Body -->
                <tbody>
                    <?php foreach ($transactions as $transaction): ?>
                        <tr class="border border-gray-300 <?= $transaction['id'] % 2 == 0 ? 'bg-gray-100' : '' ?>">
                            <td class="px-3 py-2"><?= $transaction['id'] ?></td>
                            <td class="px-3 py-2"><?= date('Y-m-d', strtotime($transaction['timestamp'])) ?></td>
                            <td class="px-3 py-2"><?= $transaction['amount'] ?></td>
                            <td class="px-3 py-2"><?= $transaction['number'] ?></td>
                            <td class="px-3 py-2"><?= $transaction['symbol'] ?></td>
                            <td class="px-3 py-2"><?= $transaction['exchange'] ?></td>
                            <td class="px-3 py-2"><?= $transaction['description'] ?></td>
                            <td class="px-3 py-2">
                                <a href="<?= $GLOBALS['BASE'] ?>/transaction/edit/<?= $transaction['id'] ?>"
                                    class="hover:bg-yellow-500 text-black p-2 rounded-lg transition duration-200 transform hover:scale-110">
                                    ✏️
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- JavaScript for Sorting and Filtering -->
    <script>
        // Sorting Function
        function sortTable(columnIndex) {
            let table = document.getElementById("transactionTable");
            let rows = Array.from(table.rows).slice(2); // Skip header & input row
            let sortedRows = rows.sort((rowA, rowB) => {
                let cellA = rowA.cells[columnIndex].innerText.trim();
                let cellB = rowB.cells[columnIndex].innerText.trim();

                if (!isNaN(cellA) && !isNaN(cellB)) { // Numeric sort
                    return Number(cellA) - Number(cellB);
                } else { // String sort
                    return cellA.localeCompare(cellB);
                }
            });

            if (table.dataset.sortedCol == columnIndex) {
                sortedRows.reverse(); // Toggle sorting order
                table.dataset.sortedCol = "";
            } else {
                table.dataset.sortedCol = columnIndex;
            }

            let tbody = table.querySelector("tbody");
            tbody.innerHTML = "";
            sortedRows.forEach(row => tbody.appendChild(row));
        }

        // Filtering Function
        document.querySelectorAll(".filter-input").forEach(input => {
            input.addEventListener("keyup", function () {
                let columnIndex = this.dataset.column;
                let filterValue = this.value.toLowerCase();
                let table = document.getElementById("transactionTable");
                let rows = table.querySelectorAll("tbody tr");

                rows.forEach(row => {
                    let cell = row.cells[columnIndex].innerText.toLowerCase();
                    row.style.display = cell.includes(filterValue) ? "" : "none";
                });
            });
        });
    </script>
</body>

</html>