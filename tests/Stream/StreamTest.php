<?php
declare(strict_types = 1);

namespace Tests\Innmind\Stream\Stream;

use Innmind\Stream\{
    Stream\Stream,
    Stream as StreamInterface,
    Stream\Position,
    Stream\Position\Mode,
    Stream\Size
};
use Innmind\Immutable\Str;
use PHPUnit\Framework\TestCase;

class StreamTest extends TestCase
{
    public function testInterface()
    {
        $stream = new Stream(tmpfile());

        $this->assertInstanceOf(StreamInterface::class, $stream);
    }

    /**
     * @expectedException Innmind\Stream\Exception\InvalidArgumentException
     */
    public function testThrowWhenNotAResource()
    {
        new Stream('foo');
    }

    /**
     * @expectedException Innmind\Stream\Exception\InvalidArgumentException
     */
    public function testThrowWhenNotAStream()
    {
        new Stream(imagecreatetruecolor(42, 42));
    }

    public function testPosition()
    {
        $resource = tmpfile();
        fwrite($resource, 'foobarbaz');

        $this->assertSame(9, ftell($resource));

        $stream = new Stream($resource);

        $this->assertInstanceOf(Position::class, $stream->position());
        $this->assertSame(0, $stream->position()->toInt());
    }

    public function testPositionOnceClosed()
    {
        $resource = tmpfile();
        fwrite($resource, 'foobarbaz');
        $stream = new Stream($resource);

        $this->assertSame(9, $stream->close()->position()->toInt());

        $stream = new Stream(stream_socket_server('tcp://127.0.0.1:1235'));

        $this->assertSame(0, $stream->close()->position()->toInt());
    }

    public function testSeek()
    {
        $resource = tmpfile();
        fwrite($resource, 'foobarbaz');
        $stream = new Stream($resource);

        $this->assertSame($stream, $stream->seek(new Position(3)));
        $this->assertSame(3, $stream->position()->toInt());
        $this->assertSame($stream, $stream->seek(new Position(3), Mode::fromCurrentPosition()));
        $this->assertSame(6, $stream->position()->toInt());
        $this->assertSame($stream, $stream->seek(new Position(3)));
        $this->assertSame(3, $stream->position()->toInt());
    }

    public function testSeekOnceClosed()
    {
        $resource = tmpfile();
        fwrite($resource, 'foobarbaz');

        $stream = new Stream($resource);

        $this->assertSame($stream, $stream->close()->seek(new Position(1)));
    }

    public function testRewind()
    {
        $resource = tmpfile();
        fwrite($resource, 'foobarbaz');
        $stream = new Stream($resource);
        $stream->seek(new Position(3));

        $this->assertSame($stream, $stream->rewind());
        $this->assertSame(0, $stream->position()->toInt());
    }

    public function testRewindOnceClosed()
    {
        $resource = tmpfile();
        fwrite($resource, 'foobarbaz');

        $stream = new Stream($resource);

        $this->assertSame(
            $stream,
            $stream
                ->seek(new Position(2))
                ->close()
                ->rewind()
        );
        $this->assertSame(9, $stream->position()->toInt());
    }

    public function testEnd()
    {
        $resource = tmpfile();
        fwrite($resource, 'foobarbaz');
        $stream = new Stream($resource);

        $this->assertFalse($stream->end());
        fread($resource, 10);
        $this->assertTrue($stream->end());
    }

    public function testEndOnceClosed()
    {
        $resource = tmpfile();
        fwrite($resource, 'foobarbaz');

        $stream = new Stream($resource);

        $this->assertTrue($stream->close()->end());
    }

    public function testSize()
    {
        $resource = tmpfile();
        fwrite($resource, 'foobarbaz');
        $stream = new Stream($resource);

        $this->assertTrue($stream->knowsSize());
        $this->assertInstanceOf(Size::class, $stream->size());
        $this->assertSame(9, $stream->size()->toInt());
    }

    public function testClose()
    {
        $resource = tmpfile();
        fwrite($resource, 'foobarbaz');
        $stream = new Stream($resource);

        $this->assertFalse($stream->closed());
        $this->assertSame($stream, $stream->close());
        $this->assertTrue($stream->closed());
    }

    public function testCloseFromOutside()
    {
        $resource = tmpfile();
        fwrite($resource, 'foobarbaz');
        $stream = new Stream($resource);

        fclose($resource);

        $this->assertTrue($stream->closed());
    }

    public function testCloseTwice()
    {
        $resource = tmpfile();
        fwrite($resource, 'foobarbaz');

        $stream = new Stream($resource);

        $this->assertSame($stream, $stream->close()->close());
    }
}
