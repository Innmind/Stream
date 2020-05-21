<?php
declare(strict_types = 1);

namespace Fixtures\Innmind\Stream;

use Innmind\Stream\{
    Readable\Stream,
    Stream\Position,
};
use Innmind\BlackBox\Set;

final class Readable
{
    /**
     * @return Set<Stream>
     */
    public static function any(): Set
    {
        $stream = Set\Decorate::mutable(
            static fn(string $string): Stream => Stream::ofContent($string),
            Set\Unicode::strings(),
        );

        return new Set\Either(
            $stream,
            Set\Composite::mutable(
                static function(Stream $stream, int $position): Stream {
                    $stream->seek(
                        new Position(\min($position, $stream->size()->toInt())),
                    );

                    return $stream;
                },
                $stream,
                Set\Integers::between(1, 100),
            ),
        );
    }
}
