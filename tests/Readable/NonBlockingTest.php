<?php
declare(strict_types = 1);

namespace Tests\Innmind\Stream\Readable;

use Innmind\Stream\{
    Readable\NonBlocking,
    Readable\Stream,
    Readable,
    Selectable,
    Stream\Position,
    Stream\Position\Mode,
    Stream\Size
};
use Innmind\Immutable\Str;
use PHPUnit\Framework\TestCase;

class NonBlockingTest extends TestCase
{
    public function testInterface()
    {
        $stream = new NonBlocking(new Stream(\tmpfile()));

        $this->assertInstanceOf(Readable::class, $stream);
        $this->assertInstanceOf(Selectable::class, $stream);
    }

    public function testResource()
    {
        $expected = \tmpfile();

        $this->assertSame(
            $expected,
            (new NonBlocking(new Stream($expected)))->resource()
        );
    }

    public function testRead()
    {
        $resource = \tmpfile();
        \fwrite($resource, 'foobarbaz');
        $stream = new NonBlocking(new Stream($resource));
        $text = $stream->read(3);

        $this->assertInstanceOf(Str::class, $text);
        $this->assertSame('foo', $text->toString());
        $this->assertSame('bar', $stream->read(3)->toString());
        $this->assertSame('baz', $stream->read(3)->toString());
        $this->assertSame('', $stream->read(3)->toString());
    }

    public function testReadRemaining()
    {
        $resource = \tmpfile();
        \fwrite($resource, 'foobarbaz');
        $stream = new NonBlocking(new Stream($resource));
        $stream->seek(new Position(3));
        $text = $stream->read();

        $this->assertInstanceOf(Str::class, $text);
        $this->assertSame('barbaz', $text->toString());
    }

    public function testReadLine()
    {
        $resource = \tmpfile();
        \fwrite($resource, "foo\nbar\nbaz");
        $stream = new NonBlocking(new Stream($resource));
        $line = $stream->readLine();

        $this->assertInstanceOf(Str::class, $line);
        $this->assertSame("foo\n", $line->toString());
        $this->assertSame("bar\n", $stream->readLine()->toString());
        $this->assertSame('baz', $stream->readLine()->toString());
        $this->assertSame('', $stream->readLine()->toString());
    }

    public function testPosition()
    {
        $resource = \tmpfile();
        \fwrite($resource, 'foobarbaz');

        $this->assertSame(9, \ftell($resource));

        $stream = new NonBlocking(new Stream($resource));

        $this->assertInstanceOf(Position::class, $stream->position());
        $this->assertSame(0, $stream->position()->toInt());
    }

    public function testSeek()
    {
        $resource = \tmpfile();
        \fwrite($resource, 'foobarbaz');
        $stream = new NonBlocking(new Stream($resource));

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
        $stream = new NonBlocking(new Stream($resource));
        $stream->seek(new Position(3));

        $this->assertNull($stream->rewind());
        $this->assertSame(0, $stream->position()->toInt());
    }

    public function testEnd()
    {
        $resource = \tmpfile();
        \fwrite($resource, 'foobarbaz');
        $stream = new NonBlocking(new Stream($resource));

        $this->assertFalse($stream->end());
        $stream->read();
        $this->assertTrue($stream->end());
    }

    public function testSize()
    {
        $resource = \tmpfile();
        \fwrite($resource, 'foobarbaz');
        $stream = new NonBlocking(new Stream($resource));

        $this->assertTrue($stream->knowsSize());
        $this->assertInstanceOf(Size::class, $stream->size());
        $this->assertSame(9, $stream->size()->toInt());
    }

    public function testClose()
    {
        $resource = \tmpfile();
        \fwrite($resource, 'foobarbaz');
        $stream = new NonBlocking(new Stream($resource));

        $this->assertFalse($stream->closed());
        $this->assertNull($stream->close());
        $this->assertTrue($stream->closed());
    }

    public function testStringCast()
    {
        $resource = \tmpfile();
        \fwrite($resource, 'foobarbaz');
        $stream = new NonBlocking(new Stream($resource));

        $this->assertSame('foobarbaz', $stream->toString());
    }
}
