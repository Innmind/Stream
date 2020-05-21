<?php
declare(strict_types = 1);

namespace Properties\Innmind\Stream\Readable;

use Innmind\BlackBox\Property;
use PHPUnit\Framework\Assert;

final class PositionCanNeverBeHigherThanSize implements Property
{
    public function name(): string
    {
        return 'Position can never be higher than size';
    }

    public function applicableTo(object $stream): bool
    {
        return true;
    }

    public function ensureHeldBy(object $stream): object
    {
        Assert::assertLessThanOrEqual(
            $stream->size()->toInt(),
            $stream->position()->toInt(),
        );

        return $stream;
    }
}
