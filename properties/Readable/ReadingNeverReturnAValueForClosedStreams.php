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
final class ReadingNeverReturnAValueForClosedStreams implements Property
{
    public static function any(): Set
    {
        return Set\Elements::of(new self);
    }

    public function applicableTo(object $stream): bool
    {
        return $stream->closed();
    }

    public function ensureHeldBy(Assert $assert, object $stream): object
    {
        $assert->null($stream->read()->match(
            static fn($value) => $value,
            static fn() => null,
        ));

        return $stream;
    }
}
