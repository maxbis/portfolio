<?php
require_once '../config/config.php';

class Portfolio {
    private $conn;

    public function __construct() {
        $this->conn = dbConnect();
    }
    
    /**
     * Get the portfolio overview with a proper YTD P&L calculation.
     */
    public function getPortfolio() {
        // Determine January 1st of the current year.
        $currentYear = date("Y");
        $jan1 = "$currentYear-01-01";

        // Aggregate transactions, splitting into pre- and post-Jan 1 parts.
        $sql = "SELECT 
                    symbol, b.short_name as 'broker', currency,
                    SUM(number) AS total_shares,
                    SUM(amount_home * number) AS total_cost,
                    SUM(CASE WHEN date <= ? THEN number ELSE 0 END) AS pre_shares,
                    SUM(CASE WHEN date <= ? THEN amount_home * number ELSE 0 END) AS pre_cost,
                    SUM(CASE WHEN date > ? THEN number ELSE 0 END) AS post_shares,
                    SUM(CASE WHEN date > ? THEN amount_home * number ELSE 0 END) AS post_cost
                FROM transaction
                join broker b on transaction.broker_id = b.id
                GROUP BY 1,2,3";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssss", $jan1, $jan1, $jan1, $jan1);
        $stmt->execute();
        $result = $stmt->get_result();

        $portfolio = [];
        $totalPortfolioValue = 0;
        while ($row = $result->fetch_assoc()) {
            $symbol = $row['symbol'];
            $totalShares = $row['total_shares'];
            $totalCost = $row['total_cost'];
            $avgBuyPrice = ($totalShares != 0) ? $totalCost / $totalShares : 0;
            
            if (strtoupper($symbol) === 'EUR') {
                // For cash, we simply use a fixed value.
                $latestPrice   = 1;
                $quoteDate     = null;
                $yearStartPrice = 1;
                $ytdProfitLoss = 0;
            } else {
                // Get the latest market price and its quote date.
                $quoteData = $this->getLatestQuote($symbol);
                $latestPrice = $quoteData['close'];
                $quoteDate   = $quoteData['quote_date'];
                
                // Get the year start price from the previous year's end quote.
                $yearStartData = $this->getYearStartQuote($symbol);
                $yearStartPrice = $yearStartData['close'];

                // Calculate YTD for shares held on Jan 1.
                $preShares = $row['pre_shares'];
                $ytdPre = 0;
                if ($preShares > 0) {
                    // For these shares, the baseline is the year-start price.
                    $ytdPre = $preShares * ($latestPrice - $yearStartPrice);
                }
                
                // Calculate YTD for shares purchased after Jan 1.
                $postShares = $row['post_shares'];
                $ytdPost = 0;
                if ($postShares > 0) {
                    // Use the average purchase price for post-Jan1 transactions.
                    $avgPostPrice = $row['post_cost'] / $postShares;
                    $ytdPost = $postShares * ($latestPrice - $avgPostPrice);
                }
                $ytdProfitLoss = $ytdPre + $ytdPost;
            }
            
            $latesetCurrencyPrice = 1;
            $YTDCurrencyPrice = 1;
            if ($row['currency'] == 'USD') {
                $latesetCurrencyPrice = $this->getLatestQuote('USD')['close'];
                $YTDCurrencyPrice = $this->getYearStartQuote('USD')['close'];
            }

            // Calculate the overall market value and total profit/loss.
            $totalValue = $totalShares * $latestPrice / $latesetCurrencyPrice;
            $profitLoss = $totalValue - $totalCost;

            $ytdProfitLoss = $ytdProfitLoss / $YTDCurrencyPrice;
            
            $portfolio[$symbol] = [
                'symbol'            => $symbol,
                'broker'            => $row['broker'],
                'number'            => $totalShares,
                'avg_buy_price'     => round($avgBuyPrice, 2),
                'latest_price'      => round($latestPrice, 2),
                'quote_date'        => $quoteDate,
                'exchange_rate'     => round($latesetCurrencyPrice, 2),
                'total_value'       => round($totalValue, 2),
                'profit_loss'       => round($profitLoss, 2),
                'ytd_profit_loss'   => round($ytdProfitLoss, 2)
            ];
            
            $totalPortfolioValue += $totalValue;
        }
        $stmt->close();
        
        // Calculate the percentage of the total portfolio for each holding.
        foreach ($portfolio as $symbol => $data) {
            $percent = ($totalPortfolioValue != 0) ? ($data['total_value'] / $totalPortfolioValue) * 100 : 0;
            $portfolio[$symbol]['percent_of_portfolio'] = round($percent, 2);
        }
        
        return $portfolio;
    }
    
    /**
     * Get the latest quote for a given symbol.
     */
    private function getLatestQuote($symbol) {
        $sql = "SELECT close, quote_date FROM quotes WHERE symbol = ? ORDER BY quote_date DESC LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $symbol);
        $stmt->execute();
        $result = $stmt->get_result();
        $quote = $result->fetch_assoc();
        $stmt->close();
        
        if ($quote) {
            return [
                'close'      => $quote['close'],
                'quote_date' => $quote['quote_date']
            ];
        } else {
            return [
                'close'      => 0,
                'quote_date' => null
            ];
        }
    }
    
    /**
     * Get the last known quote at or before December 31 of the previous year.
     */
    private function getYearStartQuote($symbol) {
        $previousYear = date("Y") - 1;
        $previousYearEnd = $previousYear . "-12-31";

        return $this->getQuoteOnDate($symbol, $previousYearEnd);
    }

    /**
     * Get the last known quote at or before a given date.
     * If no date is provided, the current date is used.
     */
    public function getQuoteOnDate($symbol, $date=null) {
        if (!$date) {
            $date = date("Y-m-d");
        }
        $sql = "SELECT close, quote_date FROM quotes WHERE symbol = ? AND quote_date <= ? ORDER BY quote_date DESC LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $symbol, $date);
        $stmt->execute();
        $result = $stmt->get_result();
        $quote = $result->fetch_assoc();
        $stmt->close();
        
        if ($quote) {
            return [
                'close'      => $quote['close'],
                'quote_date' => $quote['quote_date']
            ];
        } else {
            return [
                'close'      => 0,
                'quote_date' => null
            ];
        }
    }
}
