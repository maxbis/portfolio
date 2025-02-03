<?php
// Convert PHP quotes array to JSON for use in JavaScript.
$jsonQuotes = json_encode($quotes);
?>

<!-- Tailwind CSS (CDN for demo) -->
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.2.7/dist/tailwind.min.css" rel="stylesheet" />

<!-- Chart.js (CDN) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Quote Chart</h1>

    <!-- Canvas element for Chart.js -->
    <div class="bg-white shadow-sm rounded-lg p-4">
        <canvas id="quoteChart" width="400" height="200"></canvas>
    </div>
</div>

<script>
// 1. Parse the quotes from the PHP side
const quotes = JSON.parse('<?= $jsonQuotes ?>') || [];

// 2. Transform quotes into arrays for the chart
//    We want an array of labels (dates) and data (close prices).
//    Also ensure they are sorted by date if needed.

const sortedQuotes = quotes.slice().sort((a, b) => {
    // Sort by quote_date ascending
    return new Date(a.quote_date) - new Date(b.quote_date);
});

const labels = sortedQuotes.map(q => q.quote_date);
const dataClose = sortedQuotes.map(q => parseFloat(q.close));

// 3. Create Chart.js instance when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    const ctx = document.getElementById('quoteChart').getContext('2d');
    
    const chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels, // e.g. ["2023-01-01", "2023-01-02", ...]
            datasets: [{
                label: 'Close Price',
                data: dataClose, // e.g. [135.67, 136.25, ...]
                borderColor: 'rgba(59, 130, 246, 1)', // Tailwind "blue-500"
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.1,   // A little curve in the line
                fill: true,
            }]
        },
        options: {
            responsive: true,
            scales: {
                x: {
                    // Attempt to parse the x-axis as dates
                    type: 'time',
                    time: {
                        unit: 'day',
                        displayFormats: {
                            day: 'MMM d'
                        }
                    },
                    title: {
                        display: true,
                        text: 'Date'
                    }
                },
                y: {
                    title: {
                        display: true,
                        text: 'Close Price'
                    },
                    ticks: {
                        // Force numeric scale
                        callback: function(value, index, ticks) {
                            return '$' + value.toFixed(2);
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const value = context.parsed.y;
                            return 'Close: $' + value.toFixed(2);
                        }
                    }
                }
            }
        }
    });
});
</script>
