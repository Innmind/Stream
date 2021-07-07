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
        return $stream->size()->match(
            static fn() => true,
            static fn() => false,
        );
    }

    public function ensureHeldBy(object $stream): object
    {
        $stream->read(
            $stream
                ->size()
                ->map(static fn($size) => $size->toInt() + 1)
                ->match(
                    static fn($size) => $size,
                    static fn() => 0,
                ),
        );
        Assert::assertTrue($stream->end());

        return $stream;
    }
}
