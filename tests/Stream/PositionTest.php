<?php
declare(strict_types = 1);

namespace Tests\Innmind\Stream\Stream;

use Innmind\Stream\{
    Stream\Position,
    Exception\PositionCantBeNegative
};
use PHPUnit\Framework\TestCase;

class PositionTest extends TestCase
{
    public function testInterface()
    {
        $position = new Position(42);

        $this->assertSame(42, $position->toInt());
    }

    public function testThrowWhenNegative()
    {
        $this->expectException(PositionCantBeNegative::class);
        $this->expectExceptionMessage('-1');

        new Position(-1);
    }
}
