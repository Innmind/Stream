<?php
declare(strict_types = 1);

namespace Properties\Innmind\Stream\Readable;

use Innmind\Stream\{
    Stream\Position,
    Exception\PositionNotSeekable,
};
use Innmind\BlackBox\Property;
use PHPUnit\Framework\Assert;

final class SeekingPositionHigherThanSizeMustThrowAnException implements Property
{
    private int $position;

    public function __construct(int $position)
    {
        $this->position = $position;
    }

    public function name(): string
    {
        return "Seeking position {$this->position} above stream size must throw an exception";
    }

    public function applicableTo(object $stream): bool
    {
        return !$stream->closed() && $stream->size()->match(
            static fn() => true,
            static fn() => false,
        );
    }

    public function ensureHeldBy(object $stream): object
    {
        try {
            $current = $stream->position()->toInt();
            $stream->seek(
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
                static fn($e) => throw $e,
            );

            Assert::fail('It must throw an exception');
        } catch (PositionNotSeekable $e) {
            Assert::assertSame($current, $stream->position()->toInt());
        }

        return $stream;
    }
}
