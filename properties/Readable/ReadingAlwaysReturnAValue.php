<?php
declare(strict_types = 1);

namespace Properties\Innmind\Stream\Readable;

use Innmind\Immutable\Str;
use Innmind\BlackBox\Property;
use PHPUnit\Framework\Assert;

final class ReadingAlwaysReturnAValue implements Property
{
    private int $bytes;

    public function __construct(int $bytes)
    {
        $this->bytes = $bytes;
    }

    public function name(): string
    {
        return "Reading {$this->bytes} bytes always return a value";
    }

    public function applicableTo(object $stream): bool
    {
        return true;
    }

    public function ensureHeldBy(object $stream): object
    {
        Assert::assertInstanceOf(Str::class, $stream->read($this->bytes));

        return $stream;
    }
}
