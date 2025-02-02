<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Transaction</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex justify-center items-center min-h-screen">
    <div class="max-w-md w-full bg-white p-6 shadow-lg rounded-lg">
        <h1 class="text-xl font-semibold mb-4">Add New Transaction</h1>
        <form action="<?= $GLOBALS['BASE'] ?>/transaction/store" method="POST" class="space-y-4">
            
            <!-- Grid Layout for First Four Fields -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 text-sm">Amount:</label>
                    <input type="text" name="amount" class="w-20 p-1 text-sm border border-gray-300 rounded-md" required>
                </div>

                <div>
                    <label class="block text-gray-700 text-sm">Number:</label>
                    <input type="text" name="number" class="w-20 p-1 text-sm border border-gray-300 rounded-md" required>
                </div>

                <div>
                    <label class="block text-gray-700 text-sm">Symbol:</label>
                    <input type="text" name="symbol" class="w-20 p-1 text-sm border border-gray-300 rounded-md">
                </div>

                <div>
                    <label class="block text-gray-700 text-sm">Exchange:</label>
                    <input type="text" name="exchange" class="w-20 p-1 text-sm border border-gray-300 rounded-md">
                </div>
            </div>

            <!-- Description Field (Full Width) -->
            <div>
                <label class="block text-gray-700 text-sm">Description:</label>
                <textarea name="description" class="w-full p-2 border border-gray-300 rounded-md h-24"></textarea>
            </div>

            <button type="submit" class="w-full bg-blue-500 text-white p-2 rounded-md hover:bg-blue-600">
                Save
            </button>
        </form>
    </div>
</body>
</html>