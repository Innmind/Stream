<?php
declare(strict_types=1);

use Innmind\Stream\Readable\Stream;
use Properties\Innmind\Stream\Readable;
use Fixtures\Innmind\Stream\Readable as Fixture;
use Innmind\BlackBox\Set;

return static function() {
    yield properties(
        Stream::class,
        Readable::properties(),
        Fixture::any(),
    );

    foreach (Readable::list() as $property) {
        yield proof(
            Stream::class.' property',
            given(
                $property,
                Set\Either::any(
                    Fixture::any(),
                    Fixture::closed(),
                ),
            )->filter(static fn($property, $stream) => $property->applicableTo($stream)),
            static fn($assert, $property, $stream) => $property->ensureHeldBy(
                $assert,
                $stream,
            ),
        );
    }
};
