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
final class ReadingAboveSizeFlagStreamEnd implements Property
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
        $stream->read(
            $stream
                ->size()
                ->map(static fn($size) => $size->toInt() + 1)
                ->match(
                    static fn($size) => $size,
                    static fn() => 0,
                ),
        );
        $assert->true($stream->end());

        return $stream;
    }
}
