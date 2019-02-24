<?php
declare(strict_types = 1);

namespace Tests\Innmind\Stream\Watch;

use Innmind\Stream\{
    Watch\Ready,
    Selectable
};
use Innmind\Immutable\Set;
use PHPUnit\Framework\TestCase;

class ReadyTest extends TestCase
{
    public function testInterface()
    {
        $ready = new Ready(
            $read = Set::of(Selectable::class),
            $write = Set::of(Selectable::class),
            $oob = Set::of(Selectable::class)
        );

        $this->assertSame($read, $ready->toRead());
        $this->assertSame($write, $ready->toWrite());
        $this->assertSame($oob, $ready->toOutOfBand());
    }
}
