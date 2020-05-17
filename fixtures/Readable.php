<?php
declare(strict_types = 1);

namespace Fixtures\Innmind\Stream;

use Innmind\Stream\Readable\Stream;
use Innmind\BlackBox\Set;

final class Readable
{
    /**
     * @return Set<Stream>
     */
    public static function any(): Set
    {
        return Set\Decorate::mutable(
            static fn(string $string): Stream => Stream::ofContent($string),
            Set\Unicode::strings(),
        );
    }
}
