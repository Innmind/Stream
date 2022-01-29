<?php
declare(strict_types = 1);

namespace Properties\Innmind\Stream\Readable;

use Innmind\BlackBox\Property;
use PHPUnit\Framework\Assert;

final class ReadingNeverReturnAValueForClosedStreams implements Property
{
    public function name(): string
    {
        return 'Reading never return a value for closed streams';
    }

    public function applicableTo(object $stream): bool
    {
        return $stream->closed();
    }

    public function ensureHeldBy(object $stream): object
    {
        Assert::assertNull($stream->read()->match(
            static fn($value) => $value,
            static fn() => null,
        ));

        return $stream;
    }
}
