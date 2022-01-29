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
            $stream
                ->size()
                ->filter(fn($size) => ($stream->position()->toInt() + $this->position) <= $size->toInt()) // otherwise it will throw
                ->match(
                    static fn() => true,
                    static fn() => false,
                );
    }

    public function ensureHeldBy(object $stream): object
    {
        $current = $stream->position()->toInt();
        Assert::assertSame(
            $stream,
            $stream->seek(
                new Position($this->position),
                Position\Mode::fromCurrentPosition,
            )->match(
                static fn($value) => $value,
                static fn() => null,
            ),
        );
        Assert::assertGreaterThan(
            $current,
            $stream->position()->toInt(),
        );

        return $stream;
    }
}
