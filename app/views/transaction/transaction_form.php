<?php
/**
 * Expected variables:
 *   - $action: the form action URL (e.g., "/transaction/update/{$record['id']}" or "/transaction/insert")
 *   - $title: the title to show on the form (e.g., "Edit Transaction" or "Create Transaction")
 *   - $record: an associative array of field values (if editing). Can be empty for create.
 *   - $exchanges: an array of exchange options.
 */

// Get today's date in the format yyyy-mm-dd
$today = date('Y-m-d');
$caller = pathinfo(basename(debug_backtrace()[0]['file']), PATHINFO_FILENAME);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        function confirmDelete(url) {
            if (confirm('Are you sure you want to delete this transaction? This action cannot be undone.')) {
                window.location.href = url;
            }
        }
    </script>
</head>

<?php // echo "<pre>";print_r($symbols);exit; ?>

<body class="bg-gray-100 flex justify-center items-center min-h-screen">
    <div class="max-w-lg w-full bg-white p-6 shadow-lg rounded-lg">
        <h1 class="text-2xl font-semibold mb-4 text-center"><?= htmlspecialchars($title) ?></h1>

        <form action="<?= $action ?>" method="POST" class="space-y-4">

            <!-- Grid Layout for First Four Fields -->
            <div class="grid grid-cols-3 gap-4">
                <!-- date -->
                <div>
                    <label for="date">Date:</label><br>
                    <input type="date" id="transaction_date" name="date" value="<?= $record['date'] ?? '' ?>"
                        class="w-24 p-1 text-sm border border-gray-300 rounded-md" style="font-size:14px;" required>
                </div>

                <!-- broker -->
                <div>
                    <label class="block text-gray-700 text-sm">Broker:</label>
                    <select name="broker_id" class="w-24 p-1 text-sm border border-gray-300 rounded-md" required>
                        <option value="" disabled selected>-- choose --</option>
                        <?php foreach ($brokers as $broker): ?>
                            <option value="<?= $broker['id'] ?>" <?= isset($record['broker_id']) && $broker['id'] == $record['broker_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($broker['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Exchange -->
                <div>
                    <label class="block text-gray-700 text-sm">Exchange:</label>
                    <select name="exchange_id" class="w-24 p-1 text-sm border border-gray-300 rounded-md" required>
                        <option value="" disabled selected>-- choose --</option>
                        <?php foreach ($exchanges as $exchange): ?>
                            <option value="<?= $exchange['id'] ?>" <?= isset($record['exchange_id']) && $exchange['id'] == $record['exchange_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($exchange['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Symbol -->
                <div>
                    <label class="block text-gray-700 text-sm">Company</label>
                    <?php if (isset($record['symbol'])): ?>
                        <?= $record['symbol'] ?>
                    <?php else: ?>
                        <select name="symbol" class="w-24 p-1 text-sm border border-gray-300 rounded-md" required>
                            <option value="" disabled selected>-- choose --</option>
                            <?php foreach ($symbols as $symbol): ?>
                                <option value="<?= $symbol['symbol'] ?>" <?= (isset($record['symbol']) && $symbol['symbol'] == $record['symbol']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($symbol['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                </div>
                <!-- <div>
                    <label class="block text-gray-700 text-sm">Symbol:</label>
                    <input type="text" name="symbol" value="<?= $record['symbol'] ?? '' ?>"
                        class="w-24 p-1 text-sm border border-gray-300 rounded-md">
                </div> -->

                <div>
                    <label class="block text-gray-700 text-sm">Number:</label>
                    <input type="text" name="number" value="<?= $record['number'] ?? '' ?>"
                        class="w-24 p-1 text-sm border border-gray-300 rounded-md" required>
                </div>

                <div>
                    <label class="block text-gray-700 text-sm" title="Price per share">Price:</label>
                    <input type="text" id='amount' name="amount" value="<?= $record['amount'] ?? '' ?>"
                        class="w-24 p-1 text-sm border border-gray-300 rounded-md" required>
                </div>

                <div>
                    <label class="block text-gray-700 text-sm">Currency:</label>
                    <select id="currencySelect" name="currency"
                        class="w-24 p-1 text-sm border border-gray-300 rounded-md" required>
                        <option value="EUR" <?= isset($record['currency']) && $record['currency'] === 'EUR' ? 'selected' : '' ?>>EURO</option>
                        <option value="USD" <?= isset($record['currency']) && $record['currency'] === 'USD' ? 'selected' : '' ?>>USD</option>
                    </select>
                </div>

                <div>
                    <label class="block text-gray-700 text-sm">Strategy:</label>
                    <select name="strategy_id" class="w-24 p-1 text-sm border border-gray-300 rounded-md">
                        <?php foreach ($strategies as $strategy): ?>
                            <option value="<?= $strategy['id'] ?>" <?= isset($record['strategy_id']) && $strategy['id'] == $record['strategy_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($strategy['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-700 text-sm">Price EU:</label>
                    <input type="text" id='amount_home' name="amount_home" value="<?= $record['amount_home'] ?? '' ?>"
                        class="w-24 p-1 text-sm border border-gray-300 bg-gray-200 rounded-md" readonly>
                </div>

                <div>
                    <small>Costs/dividend:</small>
                    <input type="text" id='cash' name="cash" title="Costs are negative and dividends are positive" value="<?= $record['cash'] ?? '' ?>"
                        class="w-24 p-1 text-sm border border-gray-300 rounded-md" required>
                </div>

                <div>

                </div>

            </div>

            <!-- Description Field (Full Width) -->
            <div>
                <label class="block text-gray-700 text-sm">Description:</label>
                <textarea name="description"
                    class="w-full p-2 border border-gray-300 rounded-md h-24"><?= $record['description'] ?? '' ?></textarea>
            </div>

            <div class="flex justify-between">
                <a href="<?= $GLOBALS['BASE'] ?>/transaction/list"
                    class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">
                    Cancel
                </a>

                <div class="flex space-x-2">
                    <?php if (!empty($record)): ?>
                        <a href="javascript:void(0);"
                            onclick="confirmDelete('<?= $GLOBALS['BASE'] ?>/transaction/delete/<?= $record['id'] ?>')"
                            class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600">
                            Delete
                        </a>
                    <?php endif; ?>

                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
                        <?= strpos($action, 'update') !== false ? 'Update' : 'Create' ?>
                    </button>
                </div>
            </div>
        </form>
    </div>
</body>

</html>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const currencySelect = document.getElementById('currencySelect');
        const dateInput = document.getElementById('transaction_date');
        const amountInput = document.getElementById('amount');
        const homeAmount = document.getElementById('amount_home');
        const foreignPriceField = document.getElementById('amount');

        console.log(foreignPriceField);

        // Function to fetch the close price from the server using GET parameters
        function fetchClosePrice() {
            const symbol = currencySelect.value;
            const date = dateInput.value;

            console.log('AJAX fetchClosePrice:', symbol, date);

            // Only proceed if both symbol and date are provided
            if (symbol && date) {
                // Build the URL with query string parameters
                const url = `/portfolio/Quote/getApiClosePrice/${encodeURIComponent(symbol)}/${encodeURIComponent(date)}`;
                console.log(url);

                fetch(url, { method: 'GET' })
                    .then(response => response.json())
                    .then(data => {
                        console.log("Data returned: " + data.close);
                        if (data.close !== null) {
                            homeAmount.value = (foreignPriceField.value / data.close).toFixed(2);
                        } else {
                            homeAmount.value = foreignPriceField.value;
                        }
                    })
                    .catch(error => {
                        console.error('Fetch error:', error);
                        homeAmount.value = foreignPriceField.value;
                    });
            }
        }

        // Fire the get currency update when date or currency is changed
        currencySelect.addEventListener('change', fetchClosePrice);
        dateInput.addEventListener('change', fetchClosePrice);
        amountInput.addEventListener('change', fetchClosePrice);
    });
</script>