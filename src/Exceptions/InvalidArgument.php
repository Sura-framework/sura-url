<?php
declare(strict_types=1);
namespace Sura\Url\Exceptions;

use InvalidArgumentException;
use JetBrains\PhpStorm\Pure;

/**
 * Class InvalidArgument
 * @package Sura\Url\Exceptions
 */
class InvalidArgument extends InvalidArgumentException
{
    /**
     * @param string $url
     * @return static
     */
    #[Pure] public static function invalidScheme(string $url): self
    {
        return new static("The scheme `{$url}` isn't valid. It should be either `http` or `https`.");
    }

    /**
     * @return static
     */
    #[Pure] public static function segmentZeroDoesNotExist(): static
    {
        return new static("Segment 0 doesn't exist. Segments can be retrieved by using 1-based index or a negative index.");
    }
}
