<?php
require_once '../config/config.php';

abstract class GenericModel
{
    protected $conn;
    // Name of the primary table. Must be defined in the child class.
    protected $table;
    // Allowed fields for insert/update operations.
    protected $tableFields = [];
    // Optional join configuration: each join is an associative array with keys:
    // - type: e.g. 'LEFT JOIN' or 'INNER JOIN'
    // - table: the table to join, possibly with an alias
    // - on: the ON condition for the join
    // - select (optional): extra fields to select from the join
    protected $joins = [];

    public function __construct()
    {
        // Assumes that dbConnect() now returns a PDO instance.
        $this->conn = $this->dbConnectPDO();
    }

    function dbConnectPDO()
    {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8';
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
        return $pdo;
    }


    /**
     * Insert a new record.
     *
     * @return mixed The last inserted ID on success, or false on failure.
     */
    public function insert()
    {
        $columns = [];
        $placeholders = [];
        $values = [];

        foreach ($this->tableFields as $field => $type) {
            if (isset($_POST[$field])) {
                $columns[] = $field;
                $placeholders[] = '?';
                $values[] = $_POST[$field];
            }
        }

        if (empty($columns)) {
            return false;
        }

        $sql = "INSERT INTO {$this->table} (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            $errorInfo = $this->conn->errorInfo();
            throw new Exception("Failed to prepare statement: " . $errorInfo[2]);
        }

        if ($stmt->execute($values)) {
            return $this->conn->lastInsertId();
        } else {
            return false;
        }
    }

    /**
     * Update an existing record.
     *
     * @param int $id The record's ID.
     * @return bool True on success, false on failure.
     */
    public function update($id)
    {
        $fields = [];
        $values = [];

        foreach ($this->tableFields as $field => $type) {
            if (isset($_POST[$field])) {
                $fields[] = "$field = ?";
                $values[] = $_POST[$field];
            }
        }

        if (empty($fields)) {
            return false;
        }

        // Append the id parameter.
        $values[] = $id;

        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            $errorInfo = $this->conn->errorInfo();
            throw new Exception("Failed to prepare statement: " . $errorInfo[2]);
        }

        return $stmt->execute($values);
    }

    /**
     * Build the JOIN clause and extra select fields.
     *
     * @return array An array with the JOIN clause and extra SELECT fields.
     */
    protected function buildJoins()
    {
        $joinClause = "";
        $extraSelect = "";
        if (!empty($this->joins)) {
            foreach ($this->joins as $join) {
                // Expecting keys: type, table, on, and optionally select.
                if (isset($join['type'], $join['table'], $join['on'])) {
                    $joinClause .= " {$join['type']} {$join['table']} ON {$join['on']} ";
                }
                if (isset($join['select'])) {
                    // Append a comma-separated extra select field(s).
                    $extraSelect .= ", " . $join['select'];
                }
            }
        }
        return [$joinClause, $extraSelect];
    }

    /**
     * Retrieve record(s) with optional join data.
     *
     * @param int|null $id If provided, fetch a single record.
     * @return mixed A single record (associative array) or an array of records.
     */
    public function get($id = null)
    {
        list($joinClause, $extraSelect) = $this->buildJoins();

        // Build the SELECT clause.
        $sql = "SELECT {$this->table}.*{$extraSelect} FROM {$this->table} {$joinClause}";
        if ($id !== null) {
            $sql .= " WHERE {$this->table}.id = ?";
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                $errorInfo = $this->conn->errorInfo();
                throw new Exception("Failed to prepare statement: " . $errorInfo[2]);
            }
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                $errorInfo = $this->conn->errorInfo();
                throw new Exception("Failed to prepare statement: " . $errorInfo[2]);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    /**
     * Delete a record.
     *
     * @param int $id The record's ID.
     * @return bool True on success, false on failure.
     */
    public function delete($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id = ?");
        if (!$stmt) {
            $errorInfo = $this->conn->errorInfo();
            throw new Exception("Failed to prepare statement: " . $errorInfo[2]);
        }
        return $stmt->execute([$id]);
    }
}
