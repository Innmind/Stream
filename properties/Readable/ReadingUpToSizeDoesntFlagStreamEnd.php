<?php
declare(strict_types = 1);

namespace Properties\Innmind\Stream\Readable;

use Innmind\BlackBox\Property;
use PHPUnit\Framework\Assert;

final class ReadingUpToSizeDoesntFlagStreamEnd implements Property
{
    public function name(): string
    {
        return 'Reading up to the size doesn\'t flag the stream end';
    }

    public function applicableTo(object $stream): bool
    {
        return !$stream->end() && $stream->size()->match(
            static fn() => true,
            static fn() => false,
        );
    }

    public function ensureHeldBy(object $stream): object
    {
        $stream->read(
            $stream
                ->size()
                ->map(static fn($size) => $size->toInt())
                ->map(static fn($size) => $size - $stream->position()->toInt())
                ->match(
                    static fn($size) => $size,
                    static fn() => 0,
                ),
        );
        Assert::assertFalse($stream->end());

        return $stream;
    }
}
