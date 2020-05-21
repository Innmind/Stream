<?php
declare(strict_types = 1);

namespace Properties\Innmind\Stream\Readable;

use Innmind\BlackBox\Property;
use PHPUnit\Framework\Assert;

final class CastAlwaysReturnAString implements Property
{
    public function name(): string
    {
        return 'Cast always return a string';
    }

    public function applicableTo(object $stream): bool
    {
        return true;
    }

    public function ensureHeldBy(object $stream): object
    {
        Assert::assertIsString($stream->toString());

        return $stream;
    }
}
