<?php
declare(strict_types = 1);

namespace Properties\Innmind\Stream\Readable;

use Innmind\Stream\Readable;
use Innmind\BlackBox\{
    Property,
    Set,
    Runner\Assert,
};

/**
 * @implements Property<Readable>
 */
final class PositionCanNeverBeHigherThanSize implements Property
{
    public static function any(): Set
    {
        return Set\Elements::of(new self);
    }

    public function applicableTo(object $stream): bool
    {
        return true;
    }

    public function ensureHeldBy(Assert $assert, object $stream): object
    {
        $assert
            ->number($stream->position()->toInt())
            ->lessThanOrEqual($stream->size()->match(
                static fn($size) => $size->toInt(),
                static fn() => 0,
            ));

        return $stream;
    }
}
