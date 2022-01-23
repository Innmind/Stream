<?php
declare(strict_types = 1);

namespace Tests\Innmind\Stream\Writable;

use Innmind\Stream\{
    Writable\Stream,
    Writable,
    Selectable,
    Stream\Position,
    Stream\Position\Mode,
    Stream\Size,
    Exception\DataPartiallyWritten,
    Exception\FailedToWriteToStream,
    Exception\InvalidArgumentException
};
use Innmind\Immutable\{
    Str,
    SideEffect,
};
use PHPUnit\Framework\TestCase;

class StreamTest extends TestCase
{
    public function testInterface()
    {
        $stream = new Stream(\tmpfile());

        $this->assertInstanceOf(Writable::class, $stream);
        $this->assertInstanceOf(Selectable::class, $stream);
    }

    public function testThrowWhenNotAResource()
    {
        $this->expectException(InvalidArgumentException::class);

        new Stream('foo');
    }

    public function testThrowWhenNotAStream()
    {
        $this->expectException(InvalidArgumentException::class);

        new Stream(\imagecreatetruecolor(42, 42));
    }

    public function testPosition()
    {
        $resource = \tmpfile();
        \fwrite($resource, 'foobarbaz');

        $this->assertSame(9, \ftell($resource));

        $stream = new Stream($resource);

        $this->assertInstanceOf(Position::class, $stream->position());
        $this->assertSame(0, $stream->position()->toInt());
    }

    public function testSeek()
    {
        $resource = \tmpfile();
        \fwrite($resource, 'foobarbaz');
        $stream = new Stream($resource);

        $this->assertNull($stream->seek(new Position(3)));
        $this->assertSame(3, $stream->position()->toInt());
        $this->assertNull($stream->seek(new Position(3), Mode::fromCurrentPosition()));
        $this->assertSame(6, $stream->position()->toInt());
        $this->assertNull($stream->seek(new Position(3)));
        $this->assertSame(3, $stream->position()->toInt());
    }

    public function testRewind()
    {
        $resource = \tmpfile();
        \fwrite($resource, 'foobarbaz');
        $stream = new Stream($resource);
        $stream->seek(new Position(3));

        $this->assertNull($stream->rewind());
        $this->assertSame(0, $stream->position()->toInt());
    }

    public function testEnd()
    {
        $resource = \tmpfile();
        \fwrite($resource, 'foobarbaz');
        $stream = new Stream($resource);

        $this->assertFalse($stream->end());
        \fread($resource, 10);
        $this->assertTrue($stream->end());
    }

    public function testSize()
    {
        $resource = \tmpfile();
        \fwrite($resource, 'foobarbaz');
        $stream = new Stream($resource);

        $size = $stream->size()->match(
            static fn($size) => $size,
            static fn() => null,
        );
        $this->assertInstanceOf(Size::class, $size);
        $this->assertSame(9, $size->toInt());
    }

    public function testClose()
    {
        $resource = \tmpfile();
        \fwrite($resource, 'foobarbaz');
        $stream = new Stream($resource);

        $this->assertFalse($stream->closed());
        $this->assertInstanceOf(
            SideEffect::class,
            $stream->close()->match(
                static fn($value) => $value,
                static fn() => null,
            ),
        );
        $this->assertTrue($stream->closed());
    }

    public function testWrite()
    {
        $resource = \tmpfile();
        $stream = new Stream($resource);

        $this->assertSame(
            $stream,
            $stream->write(Str::of('foobarbaz'))->match(
                static fn($value) => $value,
                static fn() => null,
            ),
        );
        \fseek($resource, 0);
        $this->assertSame('foobarbaz', \stream_get_contents($resource));
    }

    public function testThrowWhenWritingToClosedStream()
    {
        $resource = \tmpfile();
        $stream = new Stream($resource);

        $this->expectException(FailedToWriteToStream::class);

        $stream->close();
        $stream->write(Str::of('foo'))->match(
            static fn() => null,
            static fn($e) => throw $e,
        );
    }

    public function testThrowWhenWriteFailed()
    {
        $resource = \fopen('php://temp', 'r');
        $stream = new Stream($resource);

        $this->expectException(FailedToWriteToStream::class);

        $stream->write(Str::of('foo'))->match(
            static fn() => null,
            static fn($e) => throw $e,
        );
    }

    public function testThrowWhenDataPartiallyWritten()
    {
        $resource = \fopen('php://temp', 'w');
        $stream = new Stream($resource);

        try {
            // because it doesn't use ASCII encoding
            $stream->write($data = Str::of('🤔'))->match(
                static fn() => null,
                static fn($e) => throw $e,
            );
            $this->fail('it should throw');
        } catch (DataPartiallyWritten $e) {
            $this->assertSame($data, $e->data());
            $this->assertSame(4, $e->written());
            $this->assertSame(
                '4 out of 1 written, it seems you are not using the correct string encoding',
                $e->getMessage(),
            );
        }
    }
}
