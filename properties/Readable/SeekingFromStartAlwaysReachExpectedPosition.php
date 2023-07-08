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
final class SeekingFromStartAlwaysReachExpectedPosition implements Property
{
    private int $position;

    public function __construct(int $position)
    {
        $this->position = $position;
    }

    public static function any(): Set
    {
        return Set\Integers::above(0)->map(static fn($position) => new self($position));
    }

    public function applicableTo(object $stream): bool
    {
        return !$stream->closed() && $stream->size()->match(
            static fn() => true,
            static fn() => false,
        );
    }

    public function ensureHeldBy(Assert $assert, object $stream): object
    {
        $assert->same(
            $stream,
            $stream->seek(
                new Position(
                    \min(
                        $this->position,
                        $stream->size()->match(
                            static fn($size) => $size->toInt(),
                            static fn() => 0,
                        ),
                    ),
                ),
                Position\Mode::fromStart,
            )->match(
                static fn($value) => $value,
                static fn() => null,
            ),
        );
        $assert->same(
            \min(
                $this->position,
                $stream->size()->match(
                    static fn($size) => $size->toInt(),
                    static fn() => 0,
                ),
            ),
            $stream->position()->toInt(),
        );

        return $stream;
    }
}
