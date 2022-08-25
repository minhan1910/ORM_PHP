<?php

namespace Core\Database;

class DB
{
    private $connection = null;

    public function __construct()
    {
        $this->connection = new \mysqli('localhost', 'root', '', 'orm');
        if ($this->connection->connect_error) {
            die($this->connection->error);
        }
    }

    public function query(string $sql = ''): \mysqli_result | bool
    {
        $result = $this->connection->query($sql);
        return $result;
    }

    // stdClass như empty Object bên java
    public function fetch(string $sql, $class = \stdClass::class): array
    {
        $result = $this->query($sql);

        if (!$result)
            throw new \Exception($this->connection->error);

        $out = [];

        while ($row = $result->fetch_object($class))
            array_push($out, $row);

        $result->free();

        return $out;
    }

    public function insertGetId(string $sql): int
    {
        $result = $this->query($sql);

        if ($result)
            return $this->connection->insert_id;

        throw new \Exception($this->connection->error);
    }

    public function insert(string $sql): int
    {
        $result = $this->query($sql);

        if ($result)
            return $this->connection->affected_rows;

        throw new \Exception($this->connection->error);
    }

    public function update(string $sql): int
    {
        $result = $this->query($sql);

        if ($result)
            return $result;

        throw new \Exception($this->connection->error);
    }

    public function delete(string $sql): int
    {
        $result = $this->query($sql);

        if ($result)
            return $this->connection->affected_rows;

        throw new \Exception($this->connection->error);
    }
}