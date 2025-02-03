<!DOCTYPE html>
<html lang="en">

<!-- <?php
echo "<pre>";
print_r($transaction);
echo "</pre><br>";
?> -->

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Transaction</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 flex justify-center items-center min-h-screen">
    <div class="max-w-lg w-full bg-white p-6 shadow-lg rounded-lg">
        <h1 class="text-2xl font-semibold mb-4 text-center">Edit Transaction</h1>

        <form action="<?= $GLOBALS['BASE'] ?>/transaction/update/<?= $transaction['id'] ?>" method="POST"
            class="space-y-4">

            <!-- Grid Layout for First Four Fields -->
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label for="timestamp">Date:</label>
                    <input type="date" name="date" id="date" value="<?= $transaction['date'] ?>">
                </div>


                <div>
                    <label class="block text-gray-700 text-sm">Symbol:</label>
                    <input type="text" name="symbol" value="<?= $transaction['symbol'] ?>"
                        class="w-24 p-1 text-sm border border-gray-300 rounded-md">
                </div>

                <div>
                    <label class="block text-gray-700 text-sm">Exchange:</label>
                    <input type="text" name="exchange" value="<?= $transaction['exchange'] ?>"
                        class="w-24 p-1 text-sm border border-gray-300 rounded-md">
                </div>

                <div>
                    <label class="block text-gray-700 text-sm">Number:</label>
                    <input type="text" name="number" value="<?= $transaction['number'] ?>"
                        class="w-24 p-1 text-sm border border-gray-300 rounded-md" required>
                </div>

                <div>
                    <label class="block text-gray-700 text-sm">Price:</label>
                    <input type="text" name="amount" value="<?= $transaction['amount'] ?>"
                        class="w-24 p-1 text-sm border border-gray-300 rounded-md" required>
                </div>

                <div>
                    <label class="block text-gray-700 text-sm">Currency:</label>
                    <input type="text" name="currency" value="<?= $transaction['currency'] ?>"
                        class="w-24 p-1 text-sm border border-gray-300 rounded-md" required>
                </div>

            </div>

            <!-- Description Field (Full Width) -->
            <div>
                <label class="block text-gray-700 text-sm">Description:</label>
                <textarea name="description"
                    class="w-full p-2 border border-gray-300 rounded-md h-24"><?= $transaction['description'] ?></textarea>
            </div>

            <div class="flex justify-between">
                <a href="<?= $GLOBALS['BASE'] ?>/transaction/list"
                    class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">
                    Cancel
                </a>

                <div class="flex space-x-2">
                    <a href="<?= $GLOBALS['BASE'] ?>/transaction/delete/<?= $transaction['id'] ?>"
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