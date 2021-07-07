<?php
declare(strict_types = 1);

namespace Properties\Innmind\Stream\Readable;

use Innmind\Stream\Stream\Position\Mode;
use Innmind\BlackBox\Property;
use PHPUnit\Framework\Assert;

final class ReadingChunkAlwaysReturnSameValue implements Property
{
    private int $chunk;

    public function __construct(int $chunk)
    {
        $this->chunk = $chunk;
    }

    public function name(): string
    {
        return "Reading chunk {$this->chunk} always return the same value";
    }

    public function applicableTo(object $stream): bool
    {
        return !$stream->closed() && // otherwise it will return an empty string
            $stream
                ->size()
                ->filter(static fn($size) => $size->toInt() !== $stream->position()->toInt()) // nothing more to read
                ->match(
                    static fn() => true,
                    static fn() => false,
                );
    }

    public function ensureHeldBy(object $stream): object
    {
        $position = $stream->position();
        $chunk = $stream->read($this->chunk);
        $stream->seek($position, Mode::fromStart());
        Assert::assertSame(
            $chunk->toString(),
            $stream->read($this->chunk)->toString(),
        );
        Assert::assertNotEmpty(
            $chunk->toString(),
            "Position at {$position->toInt()}",
        );

        return $stream;
    }
}
