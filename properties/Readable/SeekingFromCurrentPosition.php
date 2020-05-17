<?php
declare(strict_types = 1);

namespace Properties\Innmind\Stream\Readable;

use Innmind\Stream\Stream\Position;
use Innmind\BlackBox\Property;
use PHPUnit\Framework\Assert;

final class SeekingFromCurrentPosition implements Property
{
    private int $position;

    public function __construct(int $position)
    {
        $this->position = $position;
    }

    public function name(): string
    {
        return "Seeking {$this->position} from current position";
    }

    public function applicableTo(object $stream): bool
    {
        return !$stream->closed() &&
            $stream->knowsSize() &&
            ($stream->position()->toInt() + $this->position) <= $stream->size()->toInt(); // otherwise it will throw
    }

    public function ensureHeldBy(object $stream): object
    {
        $current = $stream->position()->toInt();
        Assert::assertNull($stream->seek(
            new Position($this->position),
            Position\Mode::fromCurrentPosition(),
        ));
        Assert::assertGreaterThan(
            $current,
            $stream->position()->toInt(),
        );

        return $stream;
    }
}
