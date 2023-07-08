<?php
declare(strict_types = 1);

namespace Properties\Innmind\Stream\Readable;

use Innmind\Stream\{
    Readable,
    Stream\Position\Mode,
};
use Innmind\BlackBox\{
    Property,
    Set,
    Runner\Assert,
};

/**
 * @implements Property<Readable>
 */
final class ReadingChunkAlwaysReturnSameValue implements Property
{
    private int $chunk;

    public function __construct(int $chunk)
    {
        $this->chunk = $chunk;
    }

    public static function any(): Set
    {
        return Set\Integers::between(1, 100)->map(static fn($chunk) => new self($chunk));
    }

    public function applicableTo(object $stream): bool
    {
        return !$stream->closed() && // otherwise it will return an empty string
            $stream
                ->size()
                ->filter(static fn($size) => $size->toInt() !== $stream->position()->toInt()) // nothing more to read
                ->match(
                    static fn() => true,
                    static fn() => false,
                );
    }

    public function ensureHeldBy(Assert $assert, object $stream): object
    {
        $position = $stream->position();
        $chunk = $stream->read($this->chunk)->match(
            static fn($value) => $value->toString(),
            static fn() => null,
        );
        $assert->string($chunk);
        $stream->seek($position, Mode::fromStart);
        $assert->same(
            $chunk,
            $stream->read($this->chunk)->match(
                static fn($value) => $value->toString(),
                static fn() => null,
            ),
        );
        $assert
            ->string($chunk)
            ->not()
            ->empty("Position at {$position->toInt()}");

        return $stream;
    }
}
