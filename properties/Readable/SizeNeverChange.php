<?php
declare(strict_types = 1);

namespace Properties\Innmind\Stream\Readable;

use Innmind\BlackBox\Property;
use PHPUnit\Framework\Assert;

final class SizeNeverChange implements Property
{
    public function name(): string
    {
        return 'Size never change';
    }

    public function applicableTo(object $stream): bool
    {
        return $stream->knowsSize();
    }

    public function ensureHeldBy(object $stream): object
    {
        Assert::assertSame(
            $stream->size()->toInt(),
            $stream->size()->toInt(),
        );

        return $stream;
    }
}
