<?php
// print_r(json_encode($data));exit;
$symbol='xxx';
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo $symbol; ?> Price Chart</title>
    <!-- Include Chart.js from CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <!-- Create a canvas element for the chart -->
    <canvas id="priceChart" width="800" height="400"></canvas>

    <script>
    // Convert the PHP data array into a JavaScript variable
    const dataFromPHP = <?php echo json_encode($data); ?>;

    // Extract labels (dates) and data points (prices) from the data
    const labels = dataFromPHP.map(item => item.quote_date);
    const prices = dataFromPHP.map(item => parseFloat(item.close)); // ensure price is a number

    // Get the context of the canvas element we want to select
    const ctx = document.getElementById('priceChart').getContext('2d');

    // Create a new Chart instance
    const priceChart = new Chart(ctx, {
        type: 'line', // choose the type of chart
        data: {
            labels: labels, // X-axis labels
            datasets: [{
                label: '<?php echo $symbol; ?> Price',
                data: prices, // Y-axis data points
                borderColor: 'rgb(75, 192, 192)',
                fill: false,
                tension: 0.1
            }]
        },
        options: {
            scales: {
                x: {
                    // If your quote_date is in a proper date format, you can use the time scale:
                    type: 'time',
                    time: {
                        unit: 'day'
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
                    }
                }
            }
        }
    });
    </script>
</body>
</html>
