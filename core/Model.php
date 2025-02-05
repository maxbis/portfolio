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
        $this->conn = dbConnect();
    }

    /**
     * Insert a new record.
     */
    public function insert()
    {
        $columns = [];
        $placeholders = [];
        $types = '';
        $values = [];

        foreach ($this->tableFields as $field => $type) {
            if (isset($_POST[$field])) {
                $columns[] = $field;
                $placeholders[] = '?';
                $types .= $type;
                $values[] = $_POST[$field];
            }
        }

        if (empty($columns)) {
            return false;
        }

        $sql = "INSERT INTO {$this->table} (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $this->conn->error);
        }
        $stmt->bind_param($types, ...$values);

        if ($stmt->execute()) {
            return $stmt->insert_id;
        } else {
            return false;
        }
    }

    /**
     * Update an existing record.
     */
    public function update($id)
    {
        $fields = [];
        $types = '';
        $values = [];

        foreach ($this->tableFields as $field => $type) {
            if (isset($_POST[$field])) {
                $fields[] = "$field = ?";
                $types .= $type;
                $values[] = $_POST[$field];
            }
        }

        if (empty($fields)) {
            return false;
        }

        // Append the id parameter.
        $types .= 'i';
        $values[] = $id;

        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $this->conn->error);
        }
        $stmt->bind_param($types, ...$values);
        return $stmt->execute();
    }

    /**
     * Build the JOIN clause and extra select fields.
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
     * @return mixed Single record (associative array) or an array of records.
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
                throw new Exception("Failed to prepare statement: " . $this->conn->error);
            }
            $stmt->bind_param("i", $id);
            $stmt->execute();
            return $stmt->get_result()->fetch_assoc();
        } else {
            $result = $this->conn->query($sql);
            return $result->fetch_all(MYSQLI_ASSOC);
        }
    }

    /**
     * Delete a record.
     */
    public function delete($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
