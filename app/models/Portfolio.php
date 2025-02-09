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
                    symbol, 
                    b.short_name as 'broker',
                    s.name as 'strategy',
                    -- Subquery to get the currency of the record with the oldest date for each symbol
                    (SELECT currency
                    FROM transaction t2 
                    WHERE t2.symbol = t1.symbol 
                    ORDER BY t2.date ASC 
                    LIMIT 1) AS currency,
                    -- Sum of the number of shares
                    SUM(number) AS total_shares,
                    -- Sum of the total cost in home currency
                    SUM(amount_home * number) AS total_cost,
                    -- Sum of the number of shares before January 1st
                    SUM(CASE WHEN date <= ? THEN number ELSE 0 END) AS pre_shares,
                    -- Sum of the total cost in home currency before January 1st
                    SUM(CASE WHEN date <= ? THEN amount_home * number ELSE 0 END) AS pre_cost,
                    -- Sum of the number of shares after January 1st
                    SUM(CASE WHEN date > ? THEN number ELSE 0 END) AS post_shares,
                    -- Sum of the total cost in home currency after January 1st
                    SUM(CASE WHEN date > ? THEN amount_home * number ELSE 0 END) AS post_cost,
                    -- Sum of the cash
                    SUM(CASE WHEN date > ? THEN cash ELSE 0 END) AS post_cash,
                    SUM(cash) as cash
                FROM transaction t1
                JOIN broker b ON t1.broker_id = b.id
                JOIN strategy s ON t1.strategy_id = s.id
                GROUP BY symbol, b.short_name, s.name";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssss", $jan1, $jan1, $jan1, $jan1, $jan1);
        $stmt->execute();
        $result = $stmt->get_result();

        $portfolio = [];
        $totalPortfolioValue = 0;
        while ($row = $result->fetch_assoc()) {
            $symbol = $row['symbol'];
            $totalShares = $row['total_shares'];
            $totalPastValue = $row['total_cost'];
            $cash = $row['cash'];
            $post_cash = $row['post_cash'];

            $avgBuyPrice = ($totalShares != 0) ? $totalPastValue / $totalShares : 0;

            if (strtoupper($symbol) === 'EUR') {
                // For cash, we simply use a fixed value.
                $latestPrice   = null;
                $quoteDate     = null;
                $yearStartPrice = null;
                $ytdProfitLoss = null;
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
            
            $latestExchangeRate = 1;
            $YTDCurrencyPrice = 1;
            if ($row['currency'] == 'USD') {
                $latestExchangeRate = $this->getLatestQuote('USD')['close'];
                $YTDCurrencyPrice = $this->getYearStartQuote('USD')['close'];
            }

            // Calculate the overall market value and total profit/loss.
            $totalValueNow =  $totalShares * $latestPrice / $latestExchangeRate;
            $profitLoss = $totalValueNow - $totalPastValue + $cash;

            $ytdProfitLoss = $ytdProfitLoss / $YTDCurrencyPrice + $post_cash;

            if (strtoupper($symbol) === 'EUR') {
                $profitLoss = 0;
                $ytdProfitLoss = 0;
                $spendThisYear = $this->getSumInvestmentsAfter($jan1);
                $totalValueNow = $cash - $spendThisYear;
            }

            
            
            $portfolio[$symbol] = [
                'symbol'            => $symbol,
                'broker'            => $row['broker'],
                'strategy'          => $row['strategy'],
                'number'            => $totalShares,
                'avg_buy_price'     => round($avgBuyPrice, 2),
                'latest_price'      => round($latestPrice, 2),
                'quote_date'        => $quoteDate,
                'exchange_rate'     => round($latestExchangeRate, 2),
                'total_value'       => round($totalValueNow, 2),
                'profit_loss'       => round($profitLoss, 2),
                'ytd_profit_loss'   => round($ytdProfitLoss, 2),
                'cash'              => round($cash, 2),
            ];
            
            $totalPortfolioValue += $totalValueNow;
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

    /**
     * Get the sum of all investments after January 1st of the current year.
     */
    public function getSumInvestmentsAfter($date) {

        $sql = "SELECT SUM(amount_home * number) AS total_investments
                FROM transaction
                WHERE date > ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $date);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();

        return $data['total_investments'];
    }
}