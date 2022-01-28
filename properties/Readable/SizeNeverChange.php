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
        return $stream->size()->match(
            static fn() => true,
            static fn() => false,
        );
    }

    public function ensureHeldBy(object $stream): object
    {
        Assert::assertSame(
            $stream->size()->match(
                static fn($size) => $size->toInt(),
                static fn() => null,
            ),
            $stream->size()->match(
                static fn($size) => $size->toInt(),
                static fn() => null,
            ),
        );

        return $stream;
    }
}
