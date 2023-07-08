<?php
declare(strict_types = 1);

namespace Properties\Innmind\Stream\Readable;

use Innmind\Stream\{
    Readable,
    Stream\Position,
    PositionNotSeekable,
};
use Innmind\BlackBox\{
    Property,
    Set,
    Runner\Assert,
};

/**
 * @implements Property<Readable>
 */
final class SeekingPositionHigherThanSizeMustReturnAnError implements Property
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
        $current = $stream->position()->toInt();
        $error = $stream->seek(
            new Position(
                $stream
                    ->size()
                    ->map(static fn($size) => $size->toInt())
                    ->map(fn($size) => $this->position + $size)
                    ->match(
                        static fn($size) => $size,
                        static fn() => 0,
                    ),
            ),
            Position\Mode::fromStart,
        )->match(
            static fn() => null,
            static fn($e) => $e,
        );

        $assert
            ->object($error)
            ->instance(PositionNotSeekable::class);
        $assert->same($current, $stream->position()->toInt());

        return $stream;
    }
}
