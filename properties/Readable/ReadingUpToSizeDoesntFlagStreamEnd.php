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
        return $stream->knowsSize() && !$stream->end();
    }

    public function ensureHeldBy(object $stream): object
    {
        $stream->read(
            $stream->size()->toInt() - $stream->position()->toInt(),
        );
        Assert::assertFalse($stream->end());

        return $stream;
    }
}
