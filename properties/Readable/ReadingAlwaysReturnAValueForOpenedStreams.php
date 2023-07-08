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
final class ReadingAlwaysReturnAValueForOpenedStreams implements Property
{
    private int $bytes;

    public function __construct(int $bytes)
    {
        $this->bytes = $bytes;
    }

    public static function any(): Set
    {
        // upper limit set to MAX_INT for a 32bits system as php won't
        // allow to read a stream above this size (even on 64bits systems)
        // php also tries to allocate the given amount in memory even
        // though the resource is shorter than asked (resulting in OOM)
        // The division by 1000 is here only to avoid this said OOM error
        return Set\Integers::between(0, (int) (2_147_483_647 / 1000))->map(static fn($bytes) => new self($bytes));
    }

    public function applicableTo(object $stream): bool
    {
        return !$stream->closed();
    }

    public function ensureHeldBy(Assert $assert, object $stream): object
    {
        $assert
            ->object($stream->read($this->bytes)->match(
                static fn($value) => $value,
                static fn() => null,
            ))
            ->instance(Str::class);

        return $stream;
    }
}
