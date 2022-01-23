<?php
declare(strict_types = 1);

namespace Properties\Innmind\Stream;

use Innmind\BlackBox\{
    Set,
    Property,
    Properties,
};

final class Readable
{
    /**
     * @return Set<Properties>
     */
    public static function properties(): Set
    {
        return Set\Properties::any(...self::list());
    }

    /**
     * @return list<Set<Property>>
     */
    public static function list(): array
    {
        return [
            Set\Property::of(Readable\Close::class),
            Set\Property::of(Readable\SizeNeverChange::class),
            Set\Property::of(Readable\RewindPlacePositionToZero::class),
            Set\Property::of(Readable\SeekingSizeDoesntFlagTheStreamEnd::class),
            Set\Property::of(
                Readable\SeekingFromStartAlwaysReachExpectedPosition::class,
                Set\Integers::above(0),
            ),
            Set\Property::of(
                Readable\SeekingPositionHigherThanSizeMustThrowAnException::class,
                Set\Integers::above(0),
            ),
            Set\Property::of(
                Readable\SeekingFromCurrentPosition::class,
                Set\Integers::between(1, 100),
            ),
            Set\Property::of(
                Readable\ReadingAlwaysReturnAValueForOpenedStreams::class,
                // upper limit set to MAX_INT for a 32bits system as php won't
                // allow to read a stream above this size (even on 64bits systems)
                // php also tries to allocate the given amount in memory even
                // though the resource is shorter than asked (resulting in OOM)
                // The division by 1000 is here only to avoid this said OOM error
                Set\Integers::between(0, (int) (2_147_483_647 / 1000)),
            ),
            Set\Property::of(Readable\ReadingNeverReturnAValueForClosedStreams::class),
            Set\Property::of(Readable\PositionCanNeverBeHigherThanSize::class),
            Set\Property::of(Readable\ReadingUpToSizeDoesntFlagStreamEnd::class),
            Set\Property::of(Readable\ReadingAboveSizeFlagStreamEnd::class),
            Set\Property::of(Readable\ReadingRestFlagStreamEnd::class),
            Set\Property::of(Readable\ReadingRestAlwaysReturnAValueForOpenedStreams::class),
            Set\Property::of(Readable\ReadingRestNeverReturnAValueForClosedStreams::class),
            Set\Property::of(
                Readable\ReadingChunkAlwaysReturnSameValue::class,
                Set\Integers::between(1, 100),
            ),
        ];
    }
}
