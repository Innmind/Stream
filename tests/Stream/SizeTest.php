<?php
declare(strict_types = 1);

namespace Tests\Innmind\Stream\Stream;

use Innmind\Stream\{
    Stream\Size,
    Exception\SizeCantBeNegative
};
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
        $this->assertSame($expected, $size->toString());
    }

    public function testThrowWhenNegative()
    {
        $this->expectException(SizeCantBeNegative::class);
        $this->expectExceptionMessage('-1');

        new Size(-1);
    }

    public function testSizeUnitLimitShortcut()
    {
        $this->assertSame(5, Size\Unit::bytes->times(5));
        $this->assertSame(5_120, Size\Unit::kilobytes->times(5));
        $this->assertSame(5_242_880, Size\Unit::megabytes->times(5));
        $this->assertSame(5_368_709_120, Size\Unit::gigabytes->times(5));
        $this->assertSame(5_497_558_138_880, Size\Unit::terabytes->times(5));
        $this->assertSame(5_629_499_534_213_120, Size\Unit::petabytes->times(5));
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
