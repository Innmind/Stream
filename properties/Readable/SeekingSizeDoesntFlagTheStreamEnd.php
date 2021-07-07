<?php
declare(strict_types = 1);

namespace Properties\Innmind\Stream\Readable;

use Innmind\Stream\Stream\Position;
use Innmind\BlackBox\Property;
use PHPUnit\Framework\Assert;

final class SeekingSizeDoesntFlagTheStreamEnd implements Property
{
    public function name(): string
    {
        return 'Seeking size doesn\'t flag the stream end';
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
        Assert::assertNull($stream->seek(
            new Position($stream->size()->match(
                static fn($size) => $size->toInt(),
                static fn() => 0,
            )),
            Position\Mode::fromStart(),
        ));
        Assert::assertFalse($stream->end());

        return $stream;
    }
}
