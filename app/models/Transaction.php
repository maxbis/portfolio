<?php
require_once '../config/config.php';

class Transaction
{
    private $conn;

    public $allowedFields = [
        'date' => 's',
        'currency' => 's',
        'amount' => 'd',
        'amount_home' => 'd',
        'number' => 'i',
        'symbol' => 's',
        'exchange' => 's',
        'description' => 's'
    ];

    public function __construct()
    {
        $this->conn = dbConnect();
    }

    public function insert()
    {
        // Arrays to store column names, placeholders, types, and values.
        $columns = [];
        $placeholders = [];
        $types = '';
        $values = [];

        // Loop through the allowedFields property and check for each field in $_POST.
        foreach ($this->allowedFields as $field => $type) {
            if (isset($_POST[$field])) {
                $columns[] = $field;        // Collect the column name.
                $placeholders[] = '?';      // Add a placeholder.
                $types .= $type;            // Append the bind type.
                $values[] = $_POST[$field]; // Collect the value.
            }
        }

        // If no valid fields were provided, exit early.
        if (empty($columns)) {
            return false;
        }

        // Build the final SQL query.
        $sql = "INSERT INTO transaction (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";

        // Prepare the statement.
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $this->conn->error);
        }

        // Bind the parameters dynamically.
        $stmt->bind_param($types, ...$values);

        // Execute and return the new record id if successful.
        if ($stmt->execute()) {
            return $stmt->insert_id;
        } else {
            return false;
        }
    }


    // Update an existing transaction
    public function update($id)
    {
        $fields = [];
        $types = '';
        $values = [];

        foreach ($this->allowedFields as $field => $type) {
            if (isset($_POST[$field])) {
                $fields[] = "$field = ?";
                $types .= $type;
                $values[] = $_POST[$field];
            }
        }

        // If no valid fields were provided, exit early
        if (empty($fields)) {
            return false;
        }

        // Append the id to the values and its type (assuming it's an integer)
        $types .= 'i';
        $values[] = $id;

        // Build the final SQL query
        $sql = "UPDATE transaction SET " . implode(', ', $fields) . " WHERE id = ?";

        // Prepare the statement
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $this->conn->error);
        }

        // Bind the parameters dynamically
        $stmt->bind_param($types, ...$values);

        // Execute and return the result
        return $stmt->execute();
    }

    // Fetch all transactions
    public function getAll()
    {
        $result = $this->conn->query("SELECT * FROM transaction ORDER BY timestamp DESC");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Fetch a single transaction
    public function getById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM transaction WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function delete($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM transaction WHERE id = ?");
        $stmt->bind_param("i", $id);
        $success = $stmt->execute(); // Execute returns TRUE on success, FALSE on failure
        return $success;
    }

}
