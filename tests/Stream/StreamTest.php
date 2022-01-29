<?php
declare(strict_types = 1);

namespace Tests\Innmind\Stream\Stream;

use Innmind\Stream\{
    Stream\Stream,
    Stream as StreamInterface,
    Stream\Position,
    Stream\Position\Mode,
    Stream\Size,
    PositionNotSeekable,
    Exception\InvalidArgumentException
};
use Innmind\Immutable\SideEffect;
use PHPUnit\Framework\TestCase;

class StreamTest extends TestCase
{
    public function testInterface()
    {
        $stream = Stream::of(\tmpfile());

        $this->assertInstanceOf(StreamInterface::class, $stream);
    }

    public function testThrowWhenNotAResource()
    {
        $this->expectException(InvalidArgumentException::class);

        Stream::of('foo');
    }

    public function testThrowWhenNotAStream()
    {
        $this->expectException(InvalidArgumentException::class);

        Stream::of(\imagecreatetruecolor(42, 42));
    }

    public function testPosition()
    {
        $resource = \tmpfile();
        \fwrite($resource, 'foobarbaz');

        $this->assertSame(9, \ftell($resource));

        $stream = Stream::of($resource);

        $this->assertInstanceOf(Position::class, $stream->position());
        $this->assertSame(0, $stream->position()->toInt());
    }

    public function testPositionOnceClosed()
    {
        $resource = \tmpfile();
        \fwrite($resource, 'foobarbaz');
        $stream = Stream::of($resource);
        $stream->close();

        $this->assertSame(0, $stream->position()->toInt());

        $stream = Stream::of(\stream_socket_server('tcp://127.0.0.1:1235'));
        $stream->close();

        $this->assertSame(0, $stream->position()->toInt());
    }

    public function testSeek()
    {
        $resource = \tmpfile();
        \fwrite($resource, 'foobarbaz');
        $stream = Stream::of($resource);

        $this->assertSame(
            $stream,
            $stream->seek(new Position(3))->match(
                static fn($value) => $value,
                static fn() => null,
            ),
        );
        $this->assertSame(3, $stream->position()->toInt());
        $this->assertSame(
            $stream,
            $stream->seek(new Position(3), Mode::fromCurrentPosition)->match(
                static fn($value) => $value,
                static fn() => null,
            ),
        );
        $this->assertSame(6, $stream->position()->toInt());
        $this->assertSame(
            $stream,
            $stream->seek(new Position(3))->match(
                static fn($value) => $value,
                static fn() => null,
            ),
        );
        $this->assertSame(3, $stream->position()->toInt());
    }

    public function testSeekOnceClosed()
    {
        $resource = \tmpfile();
        \fwrite($resource, 'foobarbaz');

        $stream = Stream::of($resource);
        $stream->close();

        $this->assertSame(
            $stream,
            $stream->seek(new Position(1))->match(
                static fn($value) => $value,
                static fn() => null,
            ),
        );
    }

    public function testRewind()
    {
        $resource = \tmpfile();
        \fwrite($resource, 'foobarbaz');
        $stream = Stream::of($resource);
        $stream->seek(new Position(3));

        $this->assertSame(
            $stream,
            $stream->rewind()->match(
                static fn($value) => $value,
                static fn() => null,
            ),
        );
        $this->assertSame(0, $stream->position()->toInt());
    }

    public function testRewindOnceClosed()
    {
        $resource = \tmpfile();
        \fwrite($resource, 'foobarbaz');

        $stream = Stream::of($resource);
        $stream->seek(new Position(2));
        $stream->close();

        $this->assertSame(
            $stream,
            $stream->rewind()->match(
                static fn($value) => $value,
                static fn() => null,
            ),
        );
        $this->assertSame(0, $stream->position()->toInt());
    }

    public function testEnd()
    {
        $resource = \tmpfile();
        \fwrite($resource, 'foobarbaz');
        $stream = Stream::of($resource);

        $this->assertFalse($stream->end());
        \fread($resource, 10);
        $this->assertTrue($stream->end());
    }

    public function testEndOnceClosed()
    {
        $resource = \tmpfile();
        \fwrite($resource, 'foobarbaz');

        $stream = Stream::of($resource);
        $stream->close();

        $this->assertTrue($stream->end());
    }

    public function testSize()
    {
        $resource = \tmpfile();
        \fwrite($resource, 'foobarbaz');
        $stream = Stream::of($resource);

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
        $stream = Stream::of($resource);

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

    public function testCloseFromOutside()
    {
        $resource = \tmpfile();
        \fwrite($resource, 'foobarbaz');
        $stream = Stream::of($resource);

        \fclose($resource);

        $this->assertTrue($stream->closed());
    }

    public function testCloseTwice()
    {
        $resource = \tmpfile();
        \fwrite($resource, 'foobarbaz');

        $stream = Stream::of($resource);
        $stream->close();

        $this->assertInstanceOf(
            SideEffect::class,
            $stream->close()->match(
                static fn($value) => $value,
                static fn() => null,
            ),
        );
    }

    public function testReturnErrorWhenNotSeekable()
    {
        $resource = \fopen('php://temp', 'r+');
        \fwrite($resource, 'foobarbaz');

        $stream = Stream::of($resource);

        $this->assertInstanceOf(
            PositionNotSeekable::class,
            $stream->seek(new Position(42))->match(
                static fn() => null,
                static fn($e) => $e,
            ),
        );
    }

    public function testDoesntTryToRewindStdin()
    {
        $this->assertInstanceOf(Stream::class, Stream::of(\fopen('php://stdin', 'rb')));
    }

    public function testReturnErrorWhenTryingToSeekStdin()
    {
        $this->assertInstanceOf(
            PositionNotSeekable::class,
            (Stream::of(\fopen('php://stdin', 'rb')))
                ->seek(new Position(0))
                ->match(
                    static fn() => null,
                    static fn($e) => $e,
                ),
        );
    }

    public function testDoesntTryToRewindStdout()
    {
        $this->assertInstanceOf(Stream::class, Stream::of(\fopen('php://stdout', 'rb')));
    }

    public function testReturnErrorWhenTryingToSeekStdout()
    {
        $this->assertInstanceOf(
            PositionNotSeekable::class,
            (Stream::of(\fopen('php://stdout', 'rb')))
                ->seek(new Position(0))
                ->match(
                    static fn() => null,
                    static fn($e) => $e,
                ),
        );
    }

    public function testDoesntTryToRewindStderr()
    {
        $this->assertInstanceOf(Stream::class, Stream::of(\fopen('php://stderr', 'rb')));
    }

    public function testReturnErrorWhenTryingToSeekStderr()
    {
        $this->assertInstanceOf(
            PositionNotSeekable::class,
            (Stream::of(\fopen('php://stderr', 'rb')))
                ->seek(new Position(0))
                ->match(
                    static fn() => null,
                    static fn($e) => $e,
                ),
        );
    }

    public function testReturnErrorWhenSeekingAboveResourceSizeEvenForConcreteFiles()
    {
        $path = \tempnam(\sys_get_temp_dir(), 'lazy_stream');
        \file_put_contents($path, 'lorem ipsum dolor');
        $stream = Stream::of(\fopen($path, 'r'));

        $this->assertInstanceOf(
            PositionNotSeekable::class,
            $stream->seek(new Position(42))->match(
                static fn() => null,
                static fn($e) => $e,
            ),
        );
    }
}
