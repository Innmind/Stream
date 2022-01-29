<?php
declare(strict_types = 1);

namespace Properties\Innmind\Stream\Readable;

use Innmind\BlackBox\Property;
use PHPUnit\Framework\Assert;

final class RewindPlacePositionToZero implements Property
{
    public function name(): string
    {
        return 'Rewind place position to zero';
    }

    public function applicableTo(object $stream): bool
    {
        return true;
    }

    public function ensureHeldBy(object $stream): object
    {
        Assert::assertSame(
            $stream,
            $stream->rewind()->match(
                static fn($value) => $value,
                static fn() => null,
            ),
        );
        Assert::assertSame(
            0,
            $stream->position()->toInt(),
        );

        return $stream;
    }
}
