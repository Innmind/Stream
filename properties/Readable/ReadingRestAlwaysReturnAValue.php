<?php
declare(strict_types = 1);

namespace Properties\Innmind\Stream\Readable;

use Innmind\Immutable\Str;
use Innmind\BlackBox\Property;
use PHPUnit\Framework\Assert;

final class ReadingRestAlwaysReturnAValue implements Property
{
    public function name(): string
    {
        return 'Reading rest always return a value';
    }

    public function applicableTo(object $stream): bool
    {
        return true;
    }

    public function ensureHeldBy(object $stream): object
    {
        Assert::assertInstanceOf(Str::class, $stream->read());

        return $stream;
    }
}
