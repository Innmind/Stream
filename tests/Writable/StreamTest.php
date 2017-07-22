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
    Exception\DataPartiallyWritten
};
use Innmind\Immutable\Str;
use PHPUnit\Framework\TestCase;

class StreamTest extends TestCase
{
    public function testInterface()
    {
        $stream = new Stream(tmpfile());

        $this->assertInstanceOf(Writable::class, $stream);
        $this->assertInstanceOf(Selectable::class, $stream);
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

    public function testRewind()
    {
        $resource = tmpfile();
        fwrite($resource, 'foobarbaz');
        $stream = new Stream($resource);
        $stream->seek(new Position(3));

        $this->assertSame($stream, $stream->rewind());
        $this->assertSame(0, $stream->position()->toInt());
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

    public function testWrite()
    {
        $resource = tmpfile();
        $stream = new Stream($resource);

        $this->assertSame($stream, $stream->write(new Str('foobarbaz')));
        fseek($resource, 0);
        $this->assertSame('foobarbaz', stream_get_contents($resource));
    }

    /**
     * @expectedException Innmind\Stream\Exception\FailedToWriteToStream
     */
    public function testThrowWhenWritingToClosedStream()
    {
        $resource = tmpfile();
        $stream = new Stream($resource);
        $stream
            ->close()
            ->write(new Str('foo'));
    }

    public function testThrowWhenWriteFailed()
    {
        $resource = fopen('php://temp', 'r');
        $stream = new Stream($resource);

        try {
            $stream->write($data = new Str('foo'));
            $this->fail('it should throw');
        } catch (DataPartiallyWritten $e) {
            $this->assertSame($data, $e->data());
            $this->assertSame(0, $e->written());
            $this->assertSame('0 out of 3 written', $e->getMessage());
        }
    }
}
