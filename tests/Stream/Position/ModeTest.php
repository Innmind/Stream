<?php
declare(strict_types = 1);

namespace Tests\Innmind\Stream\Stream\Position;

use Innmind\Stream\Stream\Position\Mode;
use PHPUnit\Framework\TestCase;

class ModeTest extends TestCase
{
    public function testInterface()
    {
        $this->assertSame(\SEEK_SET, Mode::fromStart->toInt());
        $this->assertSame(\SEEK_CUR, Mode::fromCurrentPosition->toInt());
    }
}
