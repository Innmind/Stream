<?php
declare(strict_types = 1);

namespace Properties\Innmind\Stream\Readable;

use Innmind\Stream\Stream\Position;
use Innmind\BlackBox\Property;
use PHPUnit\Framework\Assert;

final class SeekingFromStartAlwaysReachExpectedPosition implements Property
{
    private int $position;

    public function __construct(int $position)
    {
        $this->position = $position;
    }

    public function name(): string
    {
        return 'Seeking from start must reach position '.$this->position;
    }

    public function applicableTo(object $stream): bool
    {
        return !$stream->closed() && $stream->knowsSize();
    }

    public function ensureHeldBy(object $stream): object
    {
        Assert::assertNull($stream->seek(
            new Position(
                \min($this->position, $stream->size()->toInt()),
            ),
            Position\Mode::fromStart(),
        ));
        Assert::assertSame(
            \min($this->position, $stream->size()->toInt()),
            $stream->position()->toInt(),
        );

        return $stream;
    }
}
