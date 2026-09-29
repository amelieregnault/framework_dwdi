<?php

declare(strict_types=1);

namespace Framework;

use PDO;

abstract class Repository
{

    protected $table;
    protected string $associatedClass;

    public function __construct(protected Database $database) {}

    public function findAll(): array
    {
        $conn = $this->database->getConnection();
        $result = $conn->query("SELECT * FROM {$this->getTable()}");
        $result->setFetchMode(PDO::FETCH_CLASS, $this->associatedClass);
        return $result->fetchAll();
    }

    public function find(string $id): Model|bool
    {
        $conn = $this->database->getConnection();

        $sql = "SELECT * FROM {$this->getTable()} WHERE id=:id";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_CLASS, $this->associatedClass);
        return $stmt->fetch();
    }

    // TODO: Vérifier que cela fonctionne
    public function insert(Model $model): bool
    {

        if (! $model->validate() ) {
            return false;
        }

        $data = $model->getData();
        $sql = $this->getInsertSQL($data);

        $conn = $this->database->getConnection();
        $stmt = $conn->prepare($sql);

        $i = 1;
        foreach ($data as $value) {
            $stmt->bindValue($i++, $value, $this->getPDOParamType($value));
        }

        return $stmt->execute();
    }

    // TODO: Vérifier que cela fonctionne
    public function update(Model $model): bool
    {

        if (!$model->validate()) {
            return false;
        }

        $data = $model->getData();
        $sql = $this->getUpdateSQL($data);

        $conn = $this->database->getConnection();
        $stmt = $conn->prepare($sql);

        $i = 1;
        foreach ($data as $value) {
            $stmt->bindValue($i++, $value, $this->getPDOParamType($value));
        }

        $stmt->bindValue($i, $model->getId(), PDO::PARAM_INT);
        return $stmt->execute();
    }


    public function delete(Model $model): bool
    {
        $sql = "DELETE FROM {$this->getTable()} WHERE id=:id";
        $conn = $this->database->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(":id", $model->getId(), PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function getInsertedId(): string
    {
        $conn = $this->database->getConnection();
        return $conn->lastInsertId();
    }

    private function getTable(): string
    {
        if ($this->table !== null) {
            return $this->table;
        }

        $parts = explode("\\", $this::class);

        return strtolower(str_replace("Repository", "", array_pop($parts)));
    }

    private function getPDOParamType(mixed $value): mixed
    {
        return match (gettype($value)) {
            "boolean" => PDO::PARAM_BOOL,
            "integer" => PDO::PARAM_INT,
            "NULL" => PDO::PARAM_NULL,
            default => PDO::PARAM_STR
        };
    }

    private function getInsertSQL(array $data): string {


        $columns = implode(",", array_keys($data));
        $placeholders = implode(", ", array_fill(0, count($data), "?"));

        $sql = "INSERT INTO {$this->getTable()} ($columns)
                VALUES ($placeholders)";

        return $sql;

    }

    private function getUpdateSQL(array $data): string {

        $sql = "UPDATE {$this->getTable()}";

        unset($data['id']);

        $assignments = array_keys($data);
        array_walk($assignments, function (&$value) {
            $value = "$value = ?";
        });

        $sql .= " SET " . implode(", ", $assignments);
        $sql .= " WHERE id = ?";

        return $sql;

    }

}
