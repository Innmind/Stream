<?php
declare(strict_types = 1);

namespace Tests\Innmind\Stream\Stream;

use Innmind\Stream\Stream\Position;
use PHPUnit\Framework\TestCase;

class PositionTest extends TestCase
{
    public function testInterface()
    {
        $position = new Position(42);

        $this->assertSame(42, $position->toInt());
    }

    /**
     * @expectedException Innmind\Stream\Exception\PositionCantBeNegative
     * @expectedExceptionMessage -1
     */
    public function testThrowWhenNegative()
    {
        new Position(-1);
    }
}
