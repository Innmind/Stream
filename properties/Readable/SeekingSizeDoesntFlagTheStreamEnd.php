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
        return $stream->knowsSize() && !$stream->end();
    }

    public function ensureHeldBy(object $stream): object
    {
        Assert::assertNull($stream->seek(
            new Position($stream->size()->toInt()),
            Position\Mode::fromStart(),
        ));
        Assert::assertFalse($stream->end());

        return $stream;
    }
}
