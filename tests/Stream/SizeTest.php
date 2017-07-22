<?php
declare(strict_types = 1);

namespace Tests\Innmind\Stream\Stream;

use Innmind\Stream\Stream\Size;
use PHPUnit\Framework\TestCase;

class SizeTest extends TestCase
{
    /**
     * @dataProvider cases
     */
    public function testInterface(string $expected, int $value)
    {
        $size = new Size($value);

        $this->assertSame($value, $size->toInt());
        $this->assertSame($expected, (string) $size);
    }

    /**
     * @expectedException Innmind\Stream\Exception\SizeCantBeNegative
     * @expectedExceptionMessage -1
     */
    public function testThrowWhenNegative()
    {
        new Size(-1);
    }

    public function cases(): array
    {
        return [
            ['512B', 512],
            ['1023B', 1023],
            ['1KB', 1024],
            ['1023.999KB', (1024 ** 2)-1],
            ['1MB', 1024 ** 2],
            ['1024MB', (1024 ** 3)-1],
            ['1GB', 1024 ** 3],
            ['1024GB', (1024 ** 4)-1],
            ['1TB', 1024 ** 4],
            ['1024TB', (1024 ** 5)-1],
            ['1PB', 1024 ** 5],
            ['1024PB', (1024 ** 6)-1],
        ];
    }
}
