<?php
declare(strict_types = 1);

namespace Properties\Innmind\Stream\Readable;

use Innmind\Stream\Readable;
use Innmind\Immutable\Str;
use Innmind\BlackBox\{
    Property,
    Set,
    Runner\Assert,
};

/**
 * @implements Property<Readable>
 */
final class ReadingRestAlwaysReturnAValueForOpenedStreams implements Property
{
    public static function any(): Set
    {
        return Set\Elements::of(new self);
    }

    public function applicableTo(object $stream): bool
    {
        return !$stream->closed();
    }

    public function ensureHeldBy(Assert $assert, object $stream): object
    {
        $assert
            ->object($stream->read()->match(
                static fn($value) => $value,
                static fn() => null,
            ))
            ->instance(Str::class);

        return $stream;
    }
}
