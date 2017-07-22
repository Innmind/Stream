<?php
declare(strict_types = 1);

namespace Tests\Innmind\Stream\Stream;

use Innmind\Stream\{
    Stream\Bidirectional,
    Stream as StreamInterface,
    Readable,
    Writable,
    Selectable,
    Stream\Position,
    Stream\Position\Mode,
    Stream\Size,
    Exception\DataPartiallyWritten
};
use Innmind\Immutable\Str;
use PHPUnit\Framework\TestCase;

class BidirectionalTest extends TestCase
{
    public function testInterface()
    {
        $stream = new Bidirectional(tmpfile());

        $this->assertInstanceOf(Readable::class, $stream);
        $this->assertInstanceOf(Writable::class, $stream);
        $this->assertInstanceOf(Selectable::class, $stream);
    }

    /**
     * @expectedException Innmind\Stream\Exception\InvalidArgumentException
     */
    public function testThrowWhenNotAResource()
    {
        new Bidirectional('foo');
    }

    /**
     * @expectedException Innmind\Stream\Exception\InvalidArgumentException
     */
    public function testThrowWhenNotAStream()
    {
        new Bidirectional(imagecreatetruecolor(42, 42));
    }

    public function testPosition()
    {
        $resource = tmpfile();
        fwrite($resource, 'foobarbaz');

        $this->assertSame(9, ftell($resource));

        $stream = new Bidirectional($resource);

        $this->assertInstanceOf(Position::class, $stream->position());
        $this->assertSame(0, $stream->position()->toInt());
    }

    public function testSeek()
    {
        $resource = tmpfile();
        fwrite($resource, 'foobarbaz');
        $stream = new Bidirectional($resource);

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
        $stream = new Bidirectional($resource);
        $stream->seek(new Position(3));

        $this->assertSame($stream, $stream->rewind());
        $this->assertSame(0, $stream->position()->toInt());
    }

    public function testEnd()
    {
        $resource = tmpfile();
        fwrite($resource, 'foobarbaz');
        $stream = new Bidirectional($resource);

        $this->assertFalse($stream->end());
        fread($resource, 10);
        $this->assertTrue($stream->end());
    }

    public function testSize()
    {
        $resource = tmpfile();
        fwrite($resource, 'foobarbaz');
        $stream = new Bidirectional($resource);

        $this->assertTrue($stream->knowsSize());
        $this->assertInstanceOf(Size::class, $stream->size());
        $this->assertSame(9, $stream->size()->toInt());
    }

    public function testClose()
    {
        $resource = tmpfile();
        fwrite($resource, 'foobarbaz');
        $stream = new Bidirectional($resource);

        $this->assertFalse($stream->closed());
        $this->assertSame($stream, $stream->close());
        $this->assertTrue($stream->closed());
    }

    public function testWrite()
    {
        $resource = tmpfile();
        $stream = new Bidirectional($resource);

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
        $stream = new Bidirectional($resource);
        $stream
            ->close()
            ->write(new Str('foo'));
    }

    public function testThrowWhenWriteFailed()
    {
        $resource = fopen('php://temp', 'r');
        $stream = new Bidirectional($resource);

        try {
            $stream->write($data = new Str('foo'));
            $this->fail('it should throw');
        } catch (DataPartiallyWritten $e) {
            $this->assertSame($data, $e->data());
            $this->assertSame(0, $e->written());
            $this->assertSame('0 out of 3 written', $e->getMessage());
        }
    }

    public function testRead()
    {
        $resource = tmpfile();
        fwrite($resource, 'foobarbaz');
        $stream = new Bidirectional($resource);
        $text = $stream->read(3);

        $this->assertInstanceOf(Str::class, $text);
        $this->assertSame('foo', (string) $text);
        $this->assertSame('bar', (string) $stream->read(3));
        $this->assertSame('baz', (string) $stream->read(3));
        $this->assertSame('', (string) $stream->read(3));
    }

    public function testReadRemaining()
    {
        $resource = tmpfile();
        fwrite($resource, 'foobarbaz');
        $stream = new Bidirectional($resource);
        $text = $stream
            ->seek(new Position(3))
            ->read();

        $this->assertInstanceOf(Str::class, $text);
        $this->assertSame('barbaz', (string) $text);
    }

    public function testReadLine()
    {
        $resource = tmpfile();
        fwrite($resource, "foo\nbar\nbaz");
        $stream = new Bidirectional($resource);
        $line = $stream->readLine();

        $this->assertInstanceOf(Str::class, $line);
        $this->assertSame("foo\n", (string) $line);
        $this->assertSame("bar\n", (string) $stream->readLine());
        $this->assertSame('baz', (string) $stream->readLine());
        $this->assertSame('', (string) $stream->readLine());
    }

    public function testStringCast()
    {
        $resource = tmpfile();
        fwrite($resource, 'foobarbaz');
        $stream = new Bidirectional($resource);

        $this->assertSame('foobarbaz', (string) $stream);
    }
}
