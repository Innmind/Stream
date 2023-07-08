<?php
declare(strict_types = 1);

namespace Properties\Innmind\Stream\Readable;

use Innmind\Stream\{
    Readable,
    Stream\Position,
};
use Innmind\BlackBox\{
    Property,
    Set,
    Runner\Assert,
};

/**
 * @implements Property<Readable>
 */
final class SeekingSizeDoesntFlagTheStreamEnd implements Property
{
    public static function any(): Set
    {
        return Set\Elements::of(new self);
    }

    public function applicableTo(object $stream): bool
    {
        return !$stream->end() && $stream->size()->match(
            static fn() => true,
            static fn() => false,
        );
    }

    public function ensureHeldBy(Assert $assert, object $stream): object
    {
        $assert->same(
            $stream,
            $stream->seek(
                new Position($stream->size()->match(
                    static fn($size) => $size->toInt(),
                    static fn() => 0,
                )),
                Position\Mode::fromStart,
            )->match(
                static fn($value) => $value,
                static fn() => null,
            ),
        );
        $assert->false($stream->end());

        return $stream;
    }
}
