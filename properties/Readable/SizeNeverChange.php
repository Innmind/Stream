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
final class SizeNeverChange implements Property
{
    public static function any(): Set
    {
        return Set\Elements::of(new self);
    }

    public function applicableTo(object $stream): bool
    {
        return $stream->size()->match(
            static fn() => true,
            static fn() => false,
        );
    }

    public function ensureHeldBy(Assert $assert, object $stream): object
    {
        $assert->same(
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
