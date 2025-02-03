<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quote List</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<?php
// We no longer do any server-side slicing or pagination.
// Simply pass all quotes as JSON to the client.
$jsonQuotes = json_encode($quotes);
?>

<div class="container mx-auto px-4 py-8" id="quoteApp">
    <h1 class="text-2xl font-bold mb-6">Quotes</h1>

    <!-- Table Container -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 bg-white shadow-sm rounded-lg">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-6 py-3 text-left font-semibold text-gray-700">Symbol</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-700">Date</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-700">Close</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-700">Volume</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-700">Dividends</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-700">Split</th>
                </tr>
            </thead>
            <tbody id="quoteTableBody" class="divide-y divide-gray-200">
                <!-- JavaScript will dynamically insert rows here -->
            </tbody>
        </table>
    </div>

    <!-- Pagination Controls (filled by JS) -->
    <div id="paginationControls" class="mt-6 flex flex-wrap gap-2"></div>
</div>

<!-- JavaScript for pagination -->
<script>
    // 1. Parse quotes from PHP as JSON
    const quotes = JSON.parse('<?= $jsonQuotes ?>') || [];

    // 2. Pagination variables
    let currentPage = 1;
    const perPage = 50;
    const totalQuotes = quotes.length;
    const totalPages = totalQuotes > 0 ? Math.ceil(totalQuotes / perPage) : 1;

    /**
     * Render the table rows for the current page.
     */
    function renderQuotes() {
        const tableBody = document.getElementById('quoteTableBody');
        tableBody.innerHTML = ''; // Clear existing rows

        // Calculate start/end indices
        const startIndex = (currentPage - 1) * perPage;
        const endIndex = Math.min(startIndex + perPage, totalQuotes);

        // Loop through the quotes for the current page
        for (let i = startIndex; i < endIndex; i++) {
            const quote = quotes[i];

            // Create a row
            const row = document.createElement('tr');

            // Create cells (td) for each field
            row.innerHTML = `
                <td class="px-6 py-3 text-gray-900">${escapeHTML(quote.symbol)}</td>
                <td class="px-6 py-3 text-gray-900">${escapeHTML(quote.quote_date)}</td>
                <td class="px-6 py-3 text-gray-900">${escapeHTML(quote.close)}</td>
                <td class="px-6 py-3 text-gray-900">${escapeHTML(quote.volume)}</td>
                <td class="px-6 py-3 text-gray-900">${escapeHTML(quote.dividends)}</td>
                <td class="px-6 py-3 text-gray-900">${escapeHTML(quote.split)}</td>
            `;
            tableBody.appendChild(row);
        }
    }

    /**
     * Render the pagination controls (page buttons).
     */
    function renderPagination() {
        const container = document.getElementById('paginationControls');
        container.innerHTML = ''; // Clear existing

        if (totalPages <= 1) {
            return; // No need for pagination if only 1 page
        }

        // Generate page links
        for (let page = 1; page <= totalPages; page++) {
            const button = document.createElement('button');
            button.textContent = page;
            button.className = page === currentPage 
                ? 'px-3 py-1 bg-blue-500 text-white rounded shadow cursor-default'
                : 'px-3 py-1 border border-gray-300 text-gray-700 rounded hover:bg-blue-100';

            button.addEventListener('click', () => {
                currentPage = page;
                renderQuotes();
                renderPagination();
            });

            container.appendChild(button);
        }
    }

    /**
     * Simple helper to escape HTML special characters to avoid XSS.
     */
    function escapeHTML(text) {
        if (typeof text !== 'string') return text;
        return text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // 3. On page load, render quotes and pagination
    document.addEventListener('DOMContentLoaded', () => {
        renderQuotes();
        renderPagination();
    });
</script>

