<?php
require_once '../config/config.php';

class Transaction {
    private $conn;

    public function __construct() {
        $this->conn = dbConnect();
    }

    // Insert a new transaction
    public function insert($amount, $number, $symbol, $exchange, $description) {
        $stmt = $this->conn->prepare("
            INSERT INTO transaction (amount, number, symbol, exchange, description)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("disss", $amount, $number, $symbol, $exchange, $description);
        return $stmt->execute();
    }

    // Update an existing transaction
    public function update($id, $amount, $number, $symbol, $exchange, $description) {
        $stmt = $this->conn->prepare("
            UPDATE transaction 
            SET amount = ?, number = ?, symbol = ?, exchange = ?, description = ?
            WHERE id = ?
        ");
        $stmt->bind_param("disssi", $amount, $number, $symbol, $exchange, $description, $id);
        return $stmt->execute();
    }

    // Fetch all transactions
    public function getAll() {
        $result = $this->conn->query("SELECT * FROM transaction ORDER BY timestamp DESC");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Fetch a single transaction
    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM transaction WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM transaction WHERE id = ?");
        $stmt->bind_param("i", $id);
        $success = $stmt->execute(); // Execute returns TRUE on success, FALSE on failure
        return $success;
    }
    
}
