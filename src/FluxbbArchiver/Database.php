<?php

declare(strict_types=1);

namespace FluxbbArchiver;

class Database
{
    private \mysqli $conn;
    private string $prefix;

    public function __construct(string $host, int $port, string $user, string $password, string $database, string $prefix)
    {
        $this->prefix = $prefix;
        $this->conn = new \mysqli($host, $user, $password, $database, $port);
        if ($this->conn->connect_error) {
            throw new \RuntimeException('Database connection failed: ' . $this->conn->connect_error);
        }
        $this->conn->set_charset('utf8mb4');
    }

    public function prefix(): string
    {
        return $this->prefix;
    }

    /**
     * Execute a query and return the result.
     *
     * @return \mysqli_result|false
     */
    public function query(string $sql)
    {
        return $this->conn->query($sql);
    }

    /**
     * Fetch all rows from a query as associative arrays.
     *
     * @return array<int, array<string, mixed>>
     */
    public function fetchAll(string $sql): array
    {
        $result = $this->query($sql);
        if (!$result) {
            return [];
        }
        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * Fetch a single row.
     *
     * @return array<string, mixed>|null
     */
    public function fetchOne(string $sql): ?array
    {
        $result = $this->query($sql);
        if (!$result) {
            return null;
        }
        $row = $result->fetch_assoc();
        return $row ?: null;
    }

    /**
     * Check if a table exists.
     */
    public function tableExists(string $table): bool
    {
        $result = $this->query("SHOW TABLES LIKE '{$table}'");
        return $result && $result->num_rows > 0;
    }

    public function close(): void
    {
        $this->conn->close();
    }
}
