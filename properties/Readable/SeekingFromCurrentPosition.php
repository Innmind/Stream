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
final class SeekingFromCurrentPosition implements Property
{
    private int $position;

    public function __construct(int $position)
    {
        $this->position = $position;
    }

    public static function any(): Set
    {
        return Set\Integers::between(1, 100)->map(static fn($position) => new self($position));
    }

    public function applicableTo(object $stream): bool
    {
        return !$stream->closed() &&
            $stream
                ->size()
                ->filter(fn($size) => ($stream->position()->toInt() + $this->position) <= $size->toInt()) // otherwise it will throw
                ->match(
                    static fn() => true,
                    static fn() => false,
                );
    }

    public function ensureHeldBy(Assert $assert, object $stream): object
    {
        $current = $stream->position()->toInt();
        $assert->same(
            $stream,
            $stream->seek(
                new Position($this->position),
                Position\Mode::fromCurrentPosition,
            )->match(
                static fn($value) => $value,
                static fn() => null,
            ),
        );
        $assert
            ->number($stream->position()->toInt())
            ->greaterThan($current);

        return $stream;
    }
}
