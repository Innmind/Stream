<?php
declare(strict_types = 1);

namespace Properties\Innmind\Stream\Readable;

use Innmind\Immutable\SideEffect;
use Innmind\BlackBox\Property;
use PHPUnit\Framework\Assert;

final class Close implements Property
{
    public function name(): string
    {
        return 'Close';
    }

    public function applicableTo(object $stream): bool
    {
        return true;
    }

    public function ensureHeldBy(object $stream): object
    {
        Assert::assertInstanceOf(
            SideEffect::class,
            $stream->close()->match(
                static fn($value) => $value,
                static fn() => null,
            ),
        );
        Assert::assertTrue($stream->closed());

        return $stream;
    }
}
