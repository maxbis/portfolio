<?php
require_once '../config/config.php';

class Portfolio
{
    private $conn;

    public function __construct()
    {
        $this->conn = dbConnect();
    }

    /**
     * Get the portfolio overview.
     *
     * Returns an array where each element has:
     * - symbol
     * - number (net shares held)
     * - avg_buy_price (weighted average buy price)
     * - total_value (current market value)
     * - profit_loss (current value minus total cost basis)
     * - percent_of_portfolio (percentage of the total portfolio value)
     */
    public function getPortfolio()
    {
        // First, aggregate transactions by symbol.
        // Assumes: total_cost = sum(amount * number) and
        // average buy price = total_cost / total number of shares (if net > 0)
        $sql = "SELECT symbol, SUM(number) AS total_shares, SUM(amount * number) AS total_cost
                FROM transaction
                GROUP BY symbol";
        $result = $this->conn->query($sql);

        $portfolio = [];
        $totalPortfolioValue = 0;

        // Loop through each symbol holding and calculate values.
        while ($row = $result->fetch_assoc()) {
            $symbol = $row['symbol'];
            $totalShares = $row['total_shares'];

            // Avoid division by zero; if totalShares is 0, set average price to 0.
            $avgBuyPrice = ($totalShares != 0) ? $row['total_cost'] / $totalShares : 0;

            // Get the latest market price. For EUR (cash), assume a price of 1.
            if (strtoupper($symbol) === 'EUR') {
                $latestPrice = 1;
            } else {
                $quoteData = $this->getLatestQuote($symbol);
                $latestPrice = $quoteData['close'];
                $quoteDate = $quoteData['quote_date'];
            }

            // Calculate current total value and profit/loss.
            $totalValue = $totalShares * $latestPrice;
            $profitLoss = $totalValue - $row['total_cost'];

            // Store the values in our portfolio array.
            $portfolio[$symbol] = [
                'symbol' => $symbol,
                'number' => $totalShares,
                'avg_buy_price' => round($avgBuyPrice, 2),
                'latest_price' => round($latestPrice, 2),
                'quote_date' => $quoteDate,
                'total_value' => round($totalValue, 2),
                'profit_loss' => round($profitLoss, 2)
            ];

            $totalPortfolioValue += $totalValue;
        }

        // Now calculate the percentage each symbol represents of the total portfolio.
        foreach ($portfolio as $symbol => $data) {
            $percent = ($totalPortfolioValue != 0) ? ($data['total_value'] / $totalPortfolioValue) * 100 : 0;
            $portfolio[$symbol]['percent_of_portfolio'] = round($percent, 2);
        }

        return $portfolio;
    }

    /**
     * Helper function to get the latest quote (close price) for a given symbol.
     *
     * Returns the latest close price or 0 if no quote is found.
     */
    private function getLatestQuote($symbol)
    {
        $sql = "SELECT close, quote_date FROM quotes WHERE symbol = ? ORDER BY quote_date DESC LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $symbol);
        $stmt->execute();
        $result = $stmt->get_result();
        $quote = $result->fetch_assoc();
        $stmt->close();

        if ($quote) {
            return [
                'close' => $quote['close'],
                'quote_date' => $quote['quote_date']
            ];
        } else {
            return [
                'close' => 0,
                'quote_date' => null
            ];
        }
    }
}
