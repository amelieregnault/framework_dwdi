<?php

declare(strict_types=1);

namespace Framework;

class Session
{
    private array $session;

    public function __construct()
    {
        session_start();
        $this->session = $_SESSION;
    }

    public function set(mixed $key, mixed $value): void
    {
        $this->session[$key] = $value;
    }

    public function get(mixed $key): mixed
    {
        if (!array_key_exists($key, $this->session)) {
            return false;
        }
        return $this->session[$key];
    }
    
}