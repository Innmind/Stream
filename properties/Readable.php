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
            Readable\Close::any(),
            Readable\SizeNeverChange::any(),
            Readable\RewindPlacePositionToZero::any(),
            Readable\SeekingSizeDoesntFlagTheStreamEnd::any(),
            Readable\SeekingFromStartAlwaysReachExpectedPosition::any(),
            Readable\SeekingPositionHigherThanSizeMustReturnAnError::any(),
            Readable\SeekingFromCurrentPosition::any(),
            Readable\ReadingAlwaysReturnAValueForOpenedStreams::any(),
            Readable\ReadingNeverReturnAValueForClosedStreams::any(),
            Readable\PositionCanNeverBeHigherThanSize::any(),
            Readable\ReadingUpToSizeDoesntFlagStreamEnd::any(),
            Readable\ReadingAboveSizeFlagStreamEnd::any(),
            Readable\ReadingRestFlagStreamEnd::any(),
            Readable\ReadingRestAlwaysReturnAValueForOpenedStreams::any(),
            Readable\ReadingRestNeverReturnAValueForClosedStreams::any(),
            Readable\ReadingChunkAlwaysReturnSameValue::any(),
        ];
    }
}
