<?php

declare(strict_types=1);

namespace Framework;

use ReflectionObject;

abstract class Model
{
    protected int $id;

    protected array $errors = [];

    public function validate(): bool 
    {
        return true;
    }

     /**
     * Get the value of id
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Set the value of id
     *
     * @param int $id
     *
     * @return self
     */
    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }
    
    protected function addError(string $field, string $message)
    {
        $this->errors[$field] = $message;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getData() : array
    {
        $reflection = new ReflectionObject($this);
        $data = [];
        foreach ($reflection->getProperties() as $property) {
            if (!($property->class === Model::class)) {
                $name = $property->name;
                $getter = "get" . ucfirst($name);
                $data[$name] = $this->$getter();
            }
        }
        return $data;
    }
}
