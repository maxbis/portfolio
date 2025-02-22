<?php
$today = date('Y-m-d');
$title = ucfirst($caller) . " " . $symbol['name'];
$action = $GLOBALS['BASE'] . "/transaction/insert";

$number_constraint = "";
if ($caller == "buy") {
    $number_constraint = "min=1";
}
if ($caller == "sell") {
    $number_constraint = "max=-1";
}
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

<body class="bg-gray-100 flex justify-center items-center min-h-screen">
    <div class="max-w-lg w-full bg-white p-6 shadow-lg rounded-lg">
        <h1 class="text-2xl font-semibold mb-4 text-center"><?= htmlspecialchars($title) ?></h1>

        <form action="<?= $action ?>" method="POST" class="space-y-4">

            <input type="hidden" id="symbol" name="symbol" value="<?= $symbol['symbol'] ?>">
            <input type="hidden" id="number" name="number" value="0">
            <input type="hidden" id='amount' name="amount" value="0">

            <!-- Grid Layout for First Four Fields -->
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label for="date">Date:</label>
                    <input type="date" id="transaction_date" name="date" value="<?= $record['date'] ?? $today ?>">
                </div>

                <div>
                    <label class="block text-gray-700 text-sm">Broker:</label>
                    <select name="broker_id" class="w-24 p-1 text-sm border border-gray-300 rounded-md">
                        <?php foreach ($brokers as $broker): ?>
                            <option value="<?= $broker['id'] ?>" <?= isset($record['broker_id']) && $broker['id'] == $record['broker_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($broker['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- ToDo broker drop down -->
                <div>
                    <label class="block text-gray-700 text-sm">Exchange:</label>
                    <select name="exchange_id" class="w-24 p-1 text-sm border border-gray-300 rounded-md">
                        <?php foreach ($exchanges as $exchange): ?>
                            <option value="<?= $exchange['id'] ?>" <?= isset($record['exchange_id']) && $exchange['id'] == $record['exchange_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($exchange['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-gray-700 text-sm">Currency:</label>
                    <input type="text" id="currencySelect" name="currency" value="EUR">
    
                    </input>
                </div>

                <div>
                    <label class="block text-gray-700 text-sm">Description:</label>
                    <input type="text" name="description" value="Dividend"
                        class="w-24 p-1 text-sm border border-gray-300 bg-gray-200 rounded-md"
                        readonly><?= $record['description'] ?? '' ?></input>
                </div>

                <div>
                    <label class="block text-gray-700 text-sm">Net dividend:</label>
                    <input type="text" id="cash" name="cash" class="w-24 p-1 text-sm border border-gray-300 rounded-md"></input>
                </div>

            </div>

            <br />

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
                        &nbsp;<?= $caller ?>&nbsp;
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