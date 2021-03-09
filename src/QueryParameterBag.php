<?php
declare(strict_types=1);

namespace Sura\Url;

use JetBrains\PhpStorm\Pure;
use Sura\Url\Helpers\Arr;

/**
 * Class QueryParameterBag
 * @package Sura\Url
 */
class QueryParameterBag
{
    /** @var array */
    protected array $parameters;

    /**
     * QueryParameterBag constructor.
     * @param array $parameters
     */
    public function __construct(array $parameters = [])
    {
        $this->parameters = $parameters;
    }

    /**
     * @param string $query
     * @return static
     */
    public static function fromString(string $query = ''): self
    {
        if ($query === '') {
            return new static();
        }

        return new static(Arr::mapToAssoc(explode('&', $query), static function (string $keyValue) {
            $parts = explode('=', $keyValue, 2);

            return count($parts) === 2
                ? [$parts[0], rawurldecode($parts[1])]
                : [$parts[0], null];
        }));
    }

    /**
     * @param string $key
     * @param null $default
     * @return mixed
     */
    public function get(string $key, $default = null): mixed
    {
        return $this->parameters[$key] ?? $default;
    }

    /**
     * @param string $key
     * @return bool
     */
    #[Pure] public function has(string $key): bool
    {
        return array_key_exists($key, $this->parameters);
    }

    /**
     * @param string $key
     * @param string $value
     * @return $this
     */
    public function set(string $key, string $value): static
    {
        $this->parameters[$key] = $value;

        return $this;
    }

    /**
     * @param string $key
     * @return $this
     */
    public function unset(string $key): static
    {
        unset($this->parameters[$key]);

        return $this;
    }

    /**
     * @return array
     */
    public function all(): array
    {
        return $this->parameters;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $keyValuePairs = Arr::map($this->parameters, static function ($value, $key) {
            return "{$key}=" . rawurlencode($value);
        });

        return implode('&', $keyValuePairs);
    }
}
