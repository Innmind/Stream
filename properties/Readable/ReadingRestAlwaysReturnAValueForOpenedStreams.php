<?php
declare(strict_types = 1);

namespace Properties\Innmind\Stream\Readable;

use Innmind\Immutable\Str;
use Innmind\BlackBox\Property;
use PHPUnit\Framework\Assert;

final class ReadingRestAlwaysReturnAValueForOpenedStreams implements Property
{
    public function name(): string
    {
        return 'Reading rest always return a value for opened streams';
    }

    public function applicableTo(object $stream): bool
    {
        return !$stream->closed();
    }

    public function ensureHeldBy(object $stream): object
    {
        Assert::assertInstanceOf(Str::class, $stream->read()->match(
            static fn($value) => $value,
            static fn() => null,
        ));

        return $stream;
    }
}
