<?php
namespace App\Core;

class Request
{
    public readonly string $method;
    public readonly string $uri;
    public readonly array  $query;
    public readonly array  $post;

    public function __construct()
    {
        $this->method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $this->uri    = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $this->query  = $_GET;
        $this->post   = $_POST;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->post[$key] ?? $this->query[$key] ?? $default;
    }

    public function isPost(): bool
    {
        return $this->method === 'POST';
    }

    public function isGet(): bool
    {
        return $this->method === 'GET';
    }

    public function file(string $key): ?array
    {
        return $_FILES[$key] ?? null;
    }

    /** Sanitize a single input value (strip tags, trim) */
    public function clean(string $key, mixed $default = null): mixed
    {
        $val = $this->input($key, $default);
        return is_string($val) ? trim(strip_tags($val)) : $val;
    }
}
