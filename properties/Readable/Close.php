<?php
declare(strict_types = 1);

namespace Properties\Innmind\Stream\Readable;

use Innmind\Stream\Readable;
use Innmind\Immutable\SideEffect;
use Innmind\BlackBox\{
    Property,
    Set,
    Runner\Assert,
};

/**
 * @implements Property<Readable>
 */
final class Close implements Property
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
            ->object($stream->close()->match(
                static fn($value) => $value,
                static fn() => null,
            ))
            ->instance(SideEffect::class);
        $assert->true($stream->closed());

        return $stream;
    }
}
