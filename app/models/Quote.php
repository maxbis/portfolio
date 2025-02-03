<?php
require_once '../config/config.php';

class Quote
{
    private $conn;

    public function __construct()
    {
        $this->conn = dbConnect();
    }

    // Insert a new quote
    public function insert($symbol, $quote_date, $close, $volume, $dividends = 0.00, $split = 1.00)
    {
        $stmt = $this->conn->prepare("INSERT INTO quotes (symbol, quote_date, close, volume, dividends, split) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdiid", $symbol, $quote_date, $close, $volume, $dividends, $split);
        return $stmt->execute();
    }

    // Update an existing quote
    public function update($symbol, $quote_date, $close, $volume, $dividends, $split)
    {
        $stmt = $this->conn->prepare("UPDATE quotes SET close = ?, volume = ?, dividends = ?, split = ? WHERE symbol = ? AND quote_date = ?");
        $stmt->bind_param("diidss", $close, $volume, $dividends, $split, $symbol, $quote_date);
        return $stmt->execute();
    }

    // Fetch all quotes
    public function getAll()
    {
        $result = $this->conn->query("SELECT * FROM quotes ORDER BY quote_date DESC");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Fetch all quotes
    public function getQuotesBySymbol($symbol) {
        $stmt = $this->conn->prepare("SELECT * FROM quotes WHERE symbol = ? ORDER BY quote_date DESC");
        $stmt->bind_param("s", $symbol);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Fetch a single quote by symbol and date
    public function getBySymbolAndDate($symbol, $quote_date)
    {
        $stmt = $this->conn->prepare("SELECT * FROM quotes WHERE symbol = ? AND quote_date = ?");
        $stmt->bind_param("ss", $symbol, $quote_date);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Fetch the most recent quote for a symbol
    public function getMostRecentBySymbol($symbol)
    {
        $stmt = $this->conn->prepare("SELECT * FROM quotes WHERE symbol = ? ORDER BY quote_date DESC LIMIT 1");
        $stmt->bind_param("s", $symbol);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Fetch the most recent quotes for all symbols
    public function getMostRecentQuotes()
    {
        $result = $this->conn->query("SELECT q1.* FROM quotes q1 INNER JOIN (SELECT symbol, MAX(quote_date) as max_date FROM quotes GROUP BY symbol) q2 ON q1.symbol = q2.symbol AND q1.quote_date = q2.max_date ORDER BY q1.symbol");
        return $result->fetch_all(MYSQLI_ASSOC);
    }


    // Delete a quote by symbol and date
    public function delete($symbol, $quote_date)
    {
        $stmt = $this->conn->prepare("DELETE FROM quotes WHERE symbol = ? AND quote_date = ?");
        $stmt->bind_param("ss", $symbol, $quote_date);
        return $stmt->execute();
    }
}
