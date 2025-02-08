<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Transaction</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 flex justify-center items-center min-h-screen">
    <div class="max-w-lg w-full bg-white p-6 shadow-lg rounded-lg">
        <h1 class="text-2xl font-semibold mb-4 text-center">My Edit Transaction</h1>

        <form action="<?= $GLOBALS['BASE'] ?>/transaction/update/<?= $record['id'] ?>" method="POST"
            class="space-y-4">

            <!-- Grid Layout for First Four Fields -->
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label for="date">Date:</label>
                    <input type="date" id="transaction_date" name="date" id="date" value="<?= $record['date'] ?>">
                </div>


                <div>
                    <label class="block text-gray-700 text-sm">Symbol:</label>
                    <input type="text" name="symbol" value="<?= $record['symbol'] ?>"
                        class="w-24 p-1 text-sm border border-gray-300 rounded-md">
                </div>

                <div>
                    <label class="block text-gray-700 text-sm">Exchange:</label>
                    <select name="exchange_id" class="w-24 p-1 text-sm border border-gray-300 rounded-md">
                        <?php foreach ($exchanges as $exchange): ?>
                            <option value="<?= $exchange['id'] ?>"
                                <?= ($exchange['id'] == $record['exchange_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($exchange['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-gray-700 text-sm">Number:</label>
                    <input type="text" name="number" value="<?= $record['number'] ?>"
                        class="w-24 p-1 text-sm border border-gray-300 rounded-md" required>
                </div>

                <div>
                    <label class="block text-gray-700 text-sm">Price:</label>
                    <input type="text" id='amount' name="amount" value="<?= $record['amount'] ?>"
                        class="w-24 p-1 text-sm border border-gray-300 rounded-md" required>
                </div>

                <div>
                    <label class="block text-gray-700 text-sm">Currency:</label>
                    <select id="currencySelect" name="currency"
                        class="w-24 p-1 text-sm border border-gray-300 rounded-md" required>
                        <option value="EUR" <?= $record['currency'] === 'EUR' ? 'selected' : '' ?>>EURO</option>
                        <option value="USD" <?= $record['currency'] === 'USD' ? 'selected' : '' ?>>USD
                        </option>
                    </select>
                </div>



                <div>
                    <label class="block text-gray-700 text-sm"></label>&nbsp;
                </div>

                <div>
                    <label class="block text-gray-700 text-sm">Price EU:</label>
                    <input type="text" id='amount_home' name="amount_home" value="<?= $record['amount_home'] ?>"
                        class="w-24 p-1 text-sm border border-gray-300 rounded-md" readonly>
                </div>

            </div>

            <!-- Description Field (Full Width) -->
            <div>
                <label class="block text-gray-700 text-sm">Description:</label>
                <textarea name="description"
                    class="w-full p-2 border border-gray-300 rounded-md h-24"><?= $record['description'] ?></textarea>
            </div>

            <div class="flex justify-between">
                <a href="<?= $GLOBALS['BASE'] ?>/transaction/list"
                    class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">
                    Cancel
                </a>

                <div class="flex space-x-2">
                    <a href="<?= $GLOBALS['BASE'] ?>/transaction/delete/<?= $record['id'] ?>"
                        class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600"
                        onclick="return confirm('Are you sure you want to delete this transaction? This action cannot be undone.');">
                        Delete
                    </a>

                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
                        Update
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
                        console.log("Data returnd: " + data.close);
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

        // Fire the get curency update when date or currency is changed
        currencySelect.addEventListener('change', fetchClosePrice);
        dateInput.addEventListener('change', fetchClosePrice);
    });

</script>