<?php
require_once '../config/config.php';
require_once 'SymbolModel.php';

class Portfolio
{
    private $conn;
    private $symbolsModel;

    public function __construct()
    {
        $this->conn = dbConnect();
        $this->symbolsModel = new Symbol();
        $symbols = $this->symbolsModel->get();
        $this->symbols = array_column($symbols, 'name', 'symbol');
    }

    /**
     * Get the portfolio overview with a proper YTD P&L calculation.
     */
    public function getPortfolio($date = null)
    {
        // Determine January 1st of the current year.
        $currentYear = date("Y");
        $jan1 = "$currentYear-01-01";

        // Aggregate transactions, splitting into pre- and post-Jan 1 parts.
        $sql = "SELECT 
                    t1.symbol as 'symbol', 
                    br.short_name as 'broker',
                    min(str.name) as 'strategy',
                    min(se.name) as 'sector',
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
                JOIN broker br ON t1.broker_id = br.id
                JOIN strategy str ON t1.strategy_id = str.id
                JOIN symbol sy ON t1.symbol = sy.symbol
                JOIN sector se ON sy.sector_id = se.id
                GROUP BY t1.symbol, br.short_name";
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
            $broker = $row['broker'];

            $avgBuyPrice = ($totalShares != 0) ? $totalPastValue / $totalShares : 0;

            if (strtoupper($symbol) === 'EUR') {
                // For cash, we simply use a fixed value.
                $latestPrice = null;
                $quoteDate = null;
                $yearStartPrice = null;
                $ytdProfitLoss = null;
            } else {
                // Get the latest market price and its quote date.
                if ($date != null) {
                    $quoteData = $this->getQuoteOnDate($symbol, $date);
                    $quoteDataPrev = $this->getQuoteOnDate($symbol, $date, 1);
                } else {
                    $quoteData = $this->getLatestQuote($symbol);
                    $quoteDataPrev = $this->getLatestQuote($symbol, 1);
                }
                $latestPrice = $quoteData['close'];
                $latestPricePrev = $quoteDataPrev['close'];
                $quoteDate = $quoteData['quote_date'];

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
                if ($date != null) {
                    $latestExchangeRate = $this->getQuoteOnDate('USD', $date)['close'];
                } else {
                    $latestExchangeRate = $this->getLatestQuote('USD')['close'];
                }
                $YTDCurrencyPrice = $this->getYearStartQuote('USD')['close'];
            }

            // Calculate the overall market value and total profit/loss.
            $totalValueNow = $totalShares * $latestPrice / $latestExchangeRate;
            $profitLoss = $totalValueNow - $totalPastValue + $cash;
            $ytdProfitLoss = $ytdProfitLoss / $YTDCurrencyPrice + $post_cash;
            if ($totalValueNow) {
                $ytdProfitLossPerc = $ytdProfitLoss * 100 / ($totalValueNow-$ytdProfitLoss);
            } else {
                $ytdProfitLossPerc = 0;
            }

            if (strtoupper($symbol) === 'EUR') {
                $profitLoss = 0;
                $ytdProfitLoss = 0;
                // spend this year we want per broker!
                $spendThisYear = $this->getSumInvestmentsAfter($jan1, $broker);
                $totalValueNow = $cash - $spendThisYear;
            }

            if ($latestPricePrev == 0) {
                $delta_percentage = 0;
            } else {
                $delta_percentage = round((($latestPrice - $latestPricePrev) / $latestPricePrev) * 100, 2);
            }


            $portfolio[] = [
                'symbol' => $symbol,
                'symbol_title' => $this->symbols[$symbol] ?? '?',
                'sector' => $row['sector'],
                'broker' => $broker,
                'strategy' => $row['strategy'],
                'number' => $totalShares,
                'avg_buy_price' => round($avgBuyPrice,2),
                'latest_price' => round($latestPrice, 2),
                'latest_price_1d' => round($latestPricePrev, 2),
                'total_value_1d' => round($latestPricePrev * $totalShares / $latestExchangeRate, 2), // difference between latest_price and previous latest_price
                'total_value_1d_percent' => $delta_percentage,
                'quote_date' => $quoteDate,
                'exchange_rate' => round($latestExchangeRate, 2),
                'total_value' => round($totalValueNow, 0),
                'profit_loss' => round($profitLoss, 2),
                'ytd_profit_loss' => round($ytdProfitLoss, 2),
                'profit_loss_percent' => round($ytdProfitLossPerc, 2),
                'cash' => round($cash, 2),
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

    private function formatNumber($num)
    {
        if (!is_numeric($num)) {
            echo "No number, cannot format: $num";
            exit;
        }

        // $num = floatval($input);
        return round($num, 4);
        if ($num > -100 && $num < 100) {
            return number_format($num, 2, '.', '');
        } else {
            return number_format($num, 0, '.', '');
        }
    }

    /**
     * Get the latest quote for a given symbol.
     */
    private function getLatestQuote($symbol, $offset = null)
    {
        if ($offset) {
            $offset = "OFFSET $offset";
        } else {
            $offset = "";
        }
        $sql = "
                SELECT close, quote_date
                FROM quotes
                WHERE symbol = ?
                ORDER BY quote_date DESC LIMIT 1 $offset
            ";
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

    /**
     * Get the last known quote at or before December 31 of the previous year.
     */
    private function getYearStartQuote($symbol)
    {
        $previousYear = date("Y") - 1;
        $previousYearEnd = $previousYear . "-12-31";

        return $this->getQuoteOnDate($symbol, $previousYearEnd);
    }

    /**
     * Get the last known quote at or before a given date.
     * If no date is provided, the current date is used.
     */
    public function getQuoteOnDate($symbol, $date = null, $offset = null)
    {
        if (!$date) {
            $date = date("Y-m-d");
        }
        if ($offset) {
            $offset = "OFFSET $offset";
        } else {
            $offset = "";
        }
        $sql = "SELECT close, quote_date FROM quotes WHERE symbol = ? AND quote_date <= ? ORDER BY quote_date DESC LIMIT 1 $offset";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $symbol, $date);
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

    /**
     * Get the sum of all investments after January 1st of the current year.
     */
    public function getSumInvestmentsAfter($date, $broker)
    {

        $sql = "SELECT SUM(amount_home * number) AS total_investments
                FROM transaction t
                JOIN broker b ON t.broker_id = b.id
                WHERE date > ?
                AND b.short_name = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $date, $broker);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();

        return $data['total_investments'];
    }

    /**
     * Aggregates records by symbol.
     *
     * For each symbol:
     * - broker & strategy: taken from the record with the highest total_value.
     * - number: sum of all numbers.
     * - avg_buy_price: weighted average (using number as weight).
     * - latest_price & quote_date: taken from the record with the most recent quote_date.
     * - total_value, profit_loss, ytd_profit_loss, cash, percent_of_portfolio: summed.
     *
     * @param array $records An array of associative arrays.
     * @return array Aggregated records.
     */
    public function aggregateRecords(array $records, $groupBy = 'symbol'): array
    {
        // First, group records by symbol.
        $grouped = [];
        foreach ($records as $record) {
            $symbol = $record[$groupBy];
            if (!isset($grouped[$symbol])) {
                $grouped[$symbol] = [];
            }
            $grouped[$symbol][] = $record;
        }

        // Now process each group.
        $aggregated = [];
        foreach ($grouped as $symbol => $group) {
            // Initialize the aggregated result.
            $agg = [
                'symbol' => $symbol,
                'sector' => null,
                'symbol_title' => null,
                'broker' => null,
                'strategy' => null,
                'number' => 0,
                'avg_buy_price' => 0,
                'latest_price' => null,
                'total_value_1d' => null, // difference between latest_price and previous latest_price
                'quote_date' => null,
                'exchange_rate' => 1,
                'total_value' => 0,
                'profit_loss' => 0,
                'ytd_profit_loss' => 0,
                'profit_loss_percent' => null,
                'cash' => 0,
                'percent_of_portfolio' => 0,
            ];

            $weightedBuyPriceSum = 0;
            $maxTotalValue = null;   // to find the record with the highest total_value
            $recordHigestValue = null; // record from which to take broker and strategy

            foreach ($group as $record) {
                // Sum the number and accumulate for weighted average.
                $agg['number'] += $record['number'];
                $weightedBuyPriceSum += $record['number'] * $record['avg_buy_price'];

                // Sum monetary values.
                $agg['total_value'] += $record['total_value'];
                $agg['profit_loss'] += $record['profit_loss'];
                $agg['total_value_1d'] += $record['total_value_1d'];
                $agg['ytd_profit_loss'] += $record['ytd_profit_loss'];
                $agg['cash'] += $record['cash'];
                $agg['percent_of_portfolio'] += $record['percent_of_portfolio'];

                // Choose the record with the highest total_value for broker and strategy.
                if ($maxTotalValue === null || $record['total_value'] > $maxTotalValue) {
                    $maxTotalValue = $record['total_value'];
                    $recordHigestValue = $record;
                }

                // Choose the record with the most recent quote_date.
                // (Using strtotime to compare dates; assumes date format is compatible.)
                if ($agg['quote_date'] === null || strtotime($record['quote_date']) > strtotime($agg['quote_date'])) {
                    $agg['quote_date'] = $record['quote_date'];
                    $agg['latest_price'] = $record['latest_price'];
                    $agg['exchange_rate'] = $record['exchange_rate'];
                    $agg['symbol_title'] = $record['symbol_title'];
                }
            }

            // Calculate weighted average buy price if there was any number.
            if ($agg['number'] > 0) {
                $agg['avg_buy_price'] = $weightedBuyPriceSum / $agg['number'];
            }

            // Set broker and strategy from the record with the highest total_value.
            if ($recordHigestValue !== null) {
                $agg['broker'] = '*';
                $agg['strategy'] = $recordHigestValue['strategy'];
                $agg['sector'] = $recordHigestValue['sector'];
            }



            $aggregated[] = $agg;
        }

        foreach ($aggregated as &$item) {
            if ($item['total_value'] == 0) {
                $item['profit_loss_percent'] = 0;
            } else {
                $item['profit_loss_percent'] = round(($item['ytd_profit_loss'] * 100) / ($item['total_value']-$item['ytd_profit_loss']), 2);
            }
        }
        unset($item); // break the reference after the loop

        // echo "<pre>";
        // print_r($aggregated);

        return $aggregated;
    }

}