<?php
namespace App\Core;

use PDO;

abstract class Model
{
    protected PDO    $db;
    protected string $table;
    protected string $primaryKey = 'id';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /** Find a single row by primary key */
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM `{$this->table}` WHERE `{$this->primaryKey}` = ? LIMIT 1"
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /** Find a single row matching arbitrary column => value pairs */
    public function findWhere(array $conditions): ?array
    {
        [$where, $bindings] = $this->buildWhere($conditions);
        $stmt = $this->db->prepare(
            "SELECT * FROM `{$this->table}` WHERE {$where} LIMIT 1"
        );
        $stmt->execute($bindings);
        return $stmt->fetch() ?: null;
    }

    /** Find all rows matching arbitrary conditions */
    public function where(array $conditions, string $orderBy = '', int $limit = 0): array
    {
        [$where, $bindings] = $this->buildWhere($conditions);
        $sql = "SELECT * FROM `{$this->table}` WHERE {$where}";
        if ($orderBy) $sql .= " ORDER BY {$orderBy}";
        if ($limit)   $sql .= " LIMIT {$limit}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);
        return $stmt->fetchAll();
    }

    /** Insert a row; returns the new insert ID */
    public function insert(array $data): int
    {
        $cols     = implode(', ', array_map(fn($c) => "`{$c}`", array_keys($data)));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $stmt = $this->db->prepare(
            "INSERT INTO `{$this->table}` ({$cols}) VALUES ({$placeholders})"
        );
        $stmt->execute(array_values($data));
        return (int)$this->db->lastInsertId();
    }

    /** Update rows matching conditions; returns affected row count */
    public function update(array $data, array $conditions): int
    {
        $set = implode(', ', array_map(fn($c) => "`{$c}` = ?", array_keys($data)));
        [$where, $whereBindings] = $this->buildWhere($conditions);
        $stmt = $this->db->prepare(
            "UPDATE `{$this->table}` SET {$set} WHERE {$where}"
        );
        $stmt->execute([...array_values($data), ...$whereBindings]);
        return $stmt->rowCount();
    }

    /** Delete rows matching conditions; returns affected row count */
    public function delete(array $conditions): int
    {
        [$where, $bindings] = $this->buildWhere($conditions);
        $stmt = $this->db->prepare(
            "DELETE FROM `{$this->table}` WHERE {$where}"
        );
        $stmt->execute($bindings);
        return $stmt->rowCount();
    }

    /** Build a simple AND WHERE clause from an associative array */
    private function buildWhere(array $conditions): array
    {
        $clauses  = [];
        $bindings = [];
        foreach ($conditions as $col => $val) {
            if ($val === null) {
                $clauses[] = "`{$col}` IS NULL";
            } else {
                $clauses[]  = "`{$col}` = ?";
                $bindings[] = $val;
            }
        }
        return [implode(' AND ', $clauses), $bindings];
    }
}
