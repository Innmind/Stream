<?php
declare(strict_types = 1);

namespace Properties\Innmind\Stream\Readable;

use Innmind\Immutable\Str;
use Innmind\BlackBox\Property;
use PHPUnit\Framework\Assert;

final class ReadingRestFlagStreamEnd implements Property
{
    public function name(): string
    {
        return 'Reading rest flag stream end';
    }

    public function applicableTo(object $stream): bool
    {
        return $stream->knowsSize();
    }

    public function ensureHeldBy(object $stream): object
    {
        $stream->read();
        Assert::assertTrue($stream->end());

        return $stream;
    }
}
