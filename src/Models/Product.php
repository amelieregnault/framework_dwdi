<?php

declare(strict_types=1);

namespace App\Models;

use Framework\Model;

class Product extends Model
{

    private string $name = "";
    private ?string $description = null;

    public function validate(): bool
    {
        if (empty($this->name)) {
            $this->addError("name", "Name is required");
        }

        return count($this->errors) === 0;
    }


    /**
     * Get the value of name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set the value of name
     *
     * @param string $name
     *
     * @return self
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }


    /**
     * Get the value of description
     *
     * @return ?string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Set the value of description
     *
     * @param ?string $description
     *
     * @return self
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }
}
