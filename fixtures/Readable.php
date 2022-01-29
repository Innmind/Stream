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
        return self::alterPosition(Set\Decorate::mutable(
            static fn(string $string): Stream => Stream::ofContent($string),
            Set\Unicode::strings(),
        ));
    }

    /**
     * @return Set<Stream>
     */
    public static function closed(): Set
    {
        return Set\Decorate::mutable(
            static function(string $string): Stream {
                $stream = Stream::ofContent($string);
                $stream->close();

                return $stream;
            },
            Set\Unicode::strings(),
        );
    }

    /**
     * Simulate large files
     *
     * @return Set<Stream>
     */
    public static function large(): Set
    {
        return self::alterPosition(Set\Composite::mutable(
            static function(string $chunk, int $repeat): Stream {
                $resource = \fopen('php://temp', 'r+');

                // repeating the same chunk as if we let BlackBox generate a
                // sequence of strings it would all be in memory resulting in an
                // OOM error
                for ($i = 0; $i < $repeat; $i++) {
                    \fwrite($resource, $chunk);
                }

                return Stream::of($resource);
            },
            Set\Unicode::strings(),
            Set\Integers::between(1, 100),
        ));
    }

    /**
     * @return Set<Stream>
     */
    private static function alterPosition(Set $streams): Set
    {
        return new Set\Either(
            $streams,
            Set\Composite::mutable(
                static function(Stream $stream, int $position): Stream {
                    $stream->seek(
                        new Position(\min(
                            $position,
                            $stream->size()->match(
                                static fn($size) => $size->toInt(),
                                static fn() => 0,
                            ),
                        )),
                    );

                    return $stream;
                },
                $streams,
                Set\Integers::between(1, 100),
            ),
        );
    }
}
