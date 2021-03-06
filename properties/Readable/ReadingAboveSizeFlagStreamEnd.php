<?php
declare(strict_types = 1);

namespace Properties\Innmind\Stream\Readable;

use Innmind\BlackBox\Property;
use PHPUnit\Framework\Assert;

final class ReadingAboveSizeFlagStreamEnd implements Property
{
    public function name(): string
    {
        return 'Reading above size flag stream end';
    }

    public function applicableTo(object $stream): bool
    {
        return $stream->knowsSize();
    }

    public function ensureHeldBy(object $stream): object
    {
        $stream->read($stream->size()->toInt() + 1);
        Assert::assertTrue($stream->end());

        return $stream;
    }
}
