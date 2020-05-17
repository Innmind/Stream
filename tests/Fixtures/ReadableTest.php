<?php
declare(strict_types = 1);

namespace Tests\Innmind\Stream\Fixtures;

use Fixtures\Innmind\Stream\Readable;
use Innmind\Stream\Readable as ReadableInterface;
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set,
};

class ReadableTest extends TestCase
{
    use BlackBox;

    public function testGenerateStreams()
    {
        $this
            ->forAll(Readable::any())
            ->then(function($stream) {
                $this->assertInstanceOf(ReadableInterface::class, $stream);
            });
    }
}
