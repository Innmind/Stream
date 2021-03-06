<?php
declare(strict_types = 1);

namespace Tests\Innmind\Stream\Stream;

use Innmind\Stream\{
    Stream\Stream,
    Stream as StreamInterface,
    Stream\Position,
    Stream\Position\Mode,
    Stream\Size,
    Exception\PositionNotSeekable,
    Exception\InvalidArgumentException
};
use PHPUnit\Framework\TestCase;

class StreamTest extends TestCase
{
    public function testInterface()
    {
        $stream = new Stream(\tmpfile());

        $this->assertInstanceOf(StreamInterface::class, $stream);
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

    public function testPositionOnceClosed()
    {
        $resource = \tmpfile();
        \fwrite($resource, 'foobarbaz');
        $stream = new Stream($resource);
        $stream->close();

        $this->assertSame(0, $stream->position()->toInt());

        $stream = new Stream(\stream_socket_server('tcp://127.0.0.1:1235'));
        $stream->close();

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

    public function testSeekOnceClosed()
    {
        $resource = \tmpfile();
        \fwrite($resource, 'foobarbaz');

        $stream = new Stream($resource);
        $stream->close();

        $this->assertNull($stream->seek(new Position(1)));
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

    public function testRewindOnceClosed()
    {
        $resource = \tmpfile();
        \fwrite($resource, 'foobarbaz');

        $stream = new Stream($resource);
        $stream->seek(new Position(2));
        $stream->close();

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

    public function testEndOnceClosed()
    {
        $resource = \tmpfile();
        \fwrite($resource, 'foobarbaz');

        $stream = new Stream($resource);
        $stream->close();

        $this->assertTrue($stream->end());
    }

    public function testSize()
    {
        $resource = \tmpfile();
        \fwrite($resource, 'foobarbaz');
        $stream = new Stream($resource);

        $this->assertTrue($stream->knowsSize());
        $this->assertInstanceOf(Size::class, $stream->size());
        $this->assertSame(9, $stream->size()->toInt());
    }

    public function testClose()
    {
        $resource = \tmpfile();
        \fwrite($resource, 'foobarbaz');
        $stream = new Stream($resource);

        $this->assertFalse($stream->closed());
        $this->assertNull($stream->close());
        $this->assertTrue($stream->closed());
    }

    public function testCloseFromOutside()
    {
        $resource = \tmpfile();
        \fwrite($resource, 'foobarbaz');
        $stream = new Stream($resource);

        \fclose($resource);

        $this->assertTrue($stream->closed());
    }

    public function testCloseTwice()
    {
        $resource = \tmpfile();
        \fwrite($resource, 'foobarbaz');

        $stream = new Stream($resource);
        $stream->close();

        $this->assertNull($stream->close());
    }

    public function testThrowWhenNotSeekable()
    {
        $resource = \fopen('php://temp', 'r+');
        \fwrite($resource, 'foobarbaz');

        $stream = new Stream($resource);

        $this->expectException(PositionNotSeekable::class);

        $stream->seek(new Position(42));
    }

    public function testDoesntTryToRewindStdin()
    {
        $this->assertInstanceOf(Stream::class, new Stream(\fopen('php://stdin', 'rb')));
    }

    public function testThrowWhenTryingToSeekStdin()
    {
        $this->expectException(PositionNotSeekable::class);

        (new Stream(\fopen('php://stdin', 'rb')))
            ->seek(new Position(0));
    }

    public function testDoesntTryToRewindStdout()
    {
        $this->assertInstanceOf(Stream::class, new Stream(\fopen('php://stdout', 'rb')));
    }

    public function testThrowWhenTryingToSeekStdout()
    {
        $this->expectException(PositionNotSeekable::class);

        (new Stream(\fopen('php://stdout', 'rb')))
            ->seek(new Position(0));
    }

    public function testDoesntTryToRewindStderr()
    {
        $this->assertInstanceOf(Stream::class, new Stream(\fopen('php://stderr', 'rb')));
    }

    public function testThrowWhenTryingToSeekStderr()
    {
        $this->expectException(PositionNotSeekable::class);

        (new Stream(\fopen('php://stderr', 'rb')))
            ->seek(new Position(0));
    }

    public function testThrowWhenSeekingAboveResourceSizeEvenForConcreteFiles()
    {
        $path = \tempnam(\sys_get_temp_dir(), 'lazy_stream');
        \file_put_contents($path, 'lorem ipsum dolor');
        $stream = new Stream(\fopen($path, 'r'));

        $this->expectException(PositionNotSeekable::class);

        $stream->seek(new Position(42));
    }
}
