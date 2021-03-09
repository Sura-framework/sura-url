<?php
declare(strict_types=1);

namespace Sura\Url;

use JetBrains\PhpStorm\Pure;
use Sura\Macroable\Macroable;
use Sura\Url\Exceptions\InvalidArgument;

/**
 * Class Url
 * @package Sura\Url
 */
class Url
{
    use Macroable;

    /** @var string */
    protected string $scheme = '';

    /** @var string */
    protected string $host = '';

    /** @var int|null */
    protected ?int $port = null;

    /** @var string */
    protected string $user = '';

    /** @var string|null */
    protected ?string $password = null;

    /** @var string */
    protected string $path = '';

    /** @var QueryParameterBag */
    protected QueryParameterBag $query;

    /** @var string */
    protected string $fragment = '';

    /**
     *
     */
    const VALID_SCHEMES = ['http', 'https', 'mailto'];

    /**
     * Url constructor.
     */
    #[Pure] public function __construct()
    {
        $this->query = new QueryParameterBag();
    }

    /**
     * @return static
     */
    #[Pure] public static function create(): static
    {
        return new static();
    }

    /**
     * @param string $url
     * @return static
     */
    public static function fromString(string $url): static
    {
        $parts = array_merge(parse_url($url));

        $url = new static();
        $url->scheme = isset($parts['scheme']) ? $url->sanitizeScheme($parts['scheme']) : '';
        $url->host = $parts['host'] ?? '';
        $url->port = $parts['port'] ?? null;
        $url->user = $parts['user'] ?? '';
        $url->password = $parts['pass'] ?? null;
        $url->path = $parts['path'] ?? '/';
        $url->query = QueryParameterBag::fromString($parts['query'] ?? '');
        $url->fragment = $parts['fragment'] ?? '';

        return $url;
    }

    /**
     * @return string
     */
    public function getScheme(): string
    {
        return $this->scheme;
    }

    /**
     * @return string
     */
    #[Pure] public function getAuthority()
    {
        $authority = $this->host;

        if ($this->getUserInfo()) {
            $authority = $this->getUserInfo() . '@' . $authority;
        }

        if ($this->port !== null) {
            $authority .= ':' . $this->port;
        }

        return $authority;
    }

    /**
     * @return string
     */
    public function getUserInfo(): string
    {
        $userInfo = $this->user;

        if ($this->password !== null) {
            $userInfo .= ':' . $this->password;
        }

        return $userInfo;
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @return int|null
     */
    public function getPort(): ?int
    {
        return $this->port;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getBasename(): string
    {
        return $this->getSegment(-1);
    }

    /**
     * @return string
     */
    public function getDirname(): string
    {
        $segments = $this->getSegments();

        array_pop($segments);

        return '/' . implode('/', $segments);
    }

    /**
     * @return string
     */
    public function getQuery(): string
    {
        return (string)$this->query;
    }

    /**
     * @param string $key
     * @param null $default
     * @return mixed
     */
    public function getQueryParameter(string $key, $default = null): mixed
    {
        return $this->query->get($key, $default);
    }

    /**
     * @param string $key
     * @return bool
     */
    #[Pure] public function hasQueryParameter(string $key): bool
    {
        return $this->query->has($key);
    }

    /**
     * @return array
     */
    #[Pure] public function getAllQueryParameters(): array
    {
        return $this->query->all();
    }

    /**
     * @param string $key
     * @param string $value
     * @return Url
     */
    public function withQueryParameter(string $key, string $value): Url
    {
        $url = clone $this;
        $url->query->unset($key);

        $url->query->set($key, $value);

        return $url;
    }

    /**
     * @param string $key
     * @return Url
     */
    public function withoutQueryParameter(string $key): Url
    {
        $url = clone $this;
        $url->query->unset($key);

        return $url;
    }

    /**
     * @return string
     */
    public function getFragment(): string
    {
        return $this->fragment;
    }

    /**
     * @return array
     */
    #[Pure] public function getSegments(): array
    {
        return explode('/', trim($this->path, '/'));
    }

    /**
     * @param int $index
     * @param null $default
     * @return mixed
     */
    public function getSegment(int $index, $default = null): mixed
    {
        $segments = $this->getSegments();

        if ($index === 0) {
            throw InvalidArgument::segmentZeroDoesNotExist();
        }

        if ($index < 0) {
            $segments = array_reverse($segments);
            $index = abs($index);
        }

        return $segments[$index - 1] ?? $default;
    }

    /**
     * @return mixed
     */
    public function getFirstSegment(): mixed
    {
        $segments = $this->getSegments();

        return $segments[0] ?? null;
    }

    /**
     * @return mixed
     */
    public function getLastSegment(): mixed
    {
        $segments = $this->getSegments();

        return end($segments);
    }

    /**
     * @param $scheme
     * @return Url
     */
    public function withScheme($scheme): Url
    {
        $url = clone $this;

        $url->scheme = $this->sanitizeScheme($scheme);

        return $url;
    }

    /**
     * @param string $scheme
     * @return string
     */
    protected function sanitizeScheme(string $scheme): string
    {
        $scheme = strtolower($scheme);

        if (!in_array($scheme, static::VALID_SCHEMES)) {
            throw InvalidArgument::invalidScheme($scheme);
        }

        return $scheme;
    }

    /**
     * @param $user
     * @param null $password
     * @return Url
     */
    public function withUserInfo($user, $password = null): Url
    {
        $url = clone $this;

        $url->user = $user;
        $url->password = $password;

        return $url;
    }

    /**
     * @param $host
     * @return Url
     */
    public function withHost($host): Url
    {
        $url = clone $this;

        $url->host = $host;

        return $url;
    }

    /**
     * @param $port
     * @return Url
     */
    public function withPort($port): Url
    {
        $url = clone $this;

        $url->port = $port;

        return $url;
    }

    /**
     * @param $path
     * @return Url
     */
    #[Pure] public function withPath($path): Url
    {
        $url = clone $this;

        if (!str_starts_with($path, '/')) {
            $path = '/' . $path;
        }

        $url->path = $path;

        return $url;
    }

    /**
     * @param string $dirname
     * @return Url
     */
    public function withDirname(string $dirname): Url
    {
        $dirname = trim($dirname, '/');

        if (!$this->getBasename()) {
            return $this->withPath($dirname);
        }

        return $this->withPath($dirname . '/' . $this->getBasename());
    }

    /**
     * @param string $basename
     * @return Url
     */
    public function withBasename(string $basename): Url
    {
        $basename = trim($basename, '/');

        if ($this->getDirname() === '/') {
            return $this->withPath('/' . $basename);
        }

        return $this->withPath($this->getDirname() . '/' . $basename);
    }

    /**
     * @param $query
     * @return Url
     */
    public function withQuery($query): Url
    {
        $url = clone $this;

        $url->query = QueryParameterBag::fromString($query);

        return $url;
    }

    /**
     * @param $fragment
     * @return Url
     */
    public function withFragment($fragment): Url
    {
        $url = clone $this;

        $url->fragment = $fragment;

        return $url;
    }

    /**
     * @param Url $url
     * @return bool
     */
    public function matches(self $url): bool
    {
        return (string)$this === (string)$url;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $url = '';

        if ($this->getScheme() !== '' && $this->getScheme() != 'mailto') {
            $url .= $this->getScheme() . '://';
        }

        if ($this->getScheme() === 'mailto' && $this->getPath() !== '') {
            $url .= $this->getScheme() . ':';
        }

        if ($this->getScheme() === '' && $this->getAuthority() !== '') {
            $url .= '//';
        }

        if ($this->getAuthority() !== '') {
            $url .= $this->getAuthority();
        }

        if ($this->getPath() !== '/') {
            $url .= $this->getPath();
        }

        if ($this->getQuery() !== '') {
            $url .= '?' . $this->getQuery();
        }

        if ($this->getFragment() !== '') {
            $url .= '#' . $this->getFragment();
        }

        return $url;
    }

    /**
     *
     */
    public function __clone()
    {
        $this->query = clone $this->query;
    }
}
