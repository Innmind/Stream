<?php
declare(strict_types = 1);

namespace Tests\Innmind\Stream\Stream\Position;

use Innmind\Stream\Stream\Position\Mode;
use PHPUnit\Framework\TestCase;

class ModeTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(Mode::class, Mode::fromStart());
        $this->assertInstanceOf(Mode::class, Mode::fromCurrentPosition());
        $this->assertSame(Mode::fromStart(), Mode::fromStart());
        $this->assertSame(Mode::fromCurrentPosition(), Mode::fromCurrentPosition());
        $this->assertNotSame(Mode::fromStart(), Mode::fromCurrentPosition());
        $this->assertSame(SEEK_SET, Mode::fromStart()->toInt());
        $this->assertSame(SEEK_CUR, Mode::fromCurrentPosition()->toInt());
    }
}
