<?php
declare(strict_types = 1);

namespace Tests\Innmind\Stream\Readable;

use Innmind\Stream\{
    Readable\Stream,
    Readable,
    Selectable,
    Stream\Position,
    Stream\Position\Mode,
    Stream\Size,
    Exception\InvalidArgumentException
};
use Innmind\Url\Path;
use Innmind\Immutable\Str;
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\PHPUnit\BlackBox;
use Fixtures\Innmind\Stream\Readable as Fixture;
use Properties\Innmind\Stream\Readable as PReadable;

class StreamTest extends TestCase
{
    use BlackBox;

    public function testInterface()
    {
        $stream = new Stream(\tmpfile());

        $this->assertInstanceOf(Readable::class, $stream);
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

    public function testResource()
    {
        $expected = \tmpfile();

        $this->assertSame($expected, (new Stream($expected))->resource());
    }

    public function testRead()
    {
        $resource = \tmpfile();
        \fwrite($resource, 'foobarbaz');
        $stream = new Stream($resource);
        $text = $stream->read(3);

        $this->assertInstanceOf(Str::class, $text);
        $this->assertSame('foo', $text->toString());
        $this->assertSame('bar', $stream->read(3)->toString());
        $this->assertSame('baz', $stream->read(3)->toString());
        $this->assertSame('', $stream->read(3)->toString());
    }

    public function testReadOnceClosed()
    {
        $resource = \tmpfile();
        \fwrite($resource, 'foobarbaz');
        $stream = new Stream($resource);
        $stream->close();

        $this->assertSame('', $stream->read()->toString());
    }

    public function testReadRemaining()
    {
        $resource = \tmpfile();
        \fwrite($resource, 'foobarbaz');
        $stream = new Stream($resource);
        $stream->seek(new Position(3));
        $text = $stream->read();

        $this->assertInstanceOf(Str::class, $text);
        $this->assertSame('barbaz', $text->toString());
    }

    public function testReadLine()
    {
        $resource = \tmpfile();
        \fwrite($resource, "foo\nbar\nbaz");
        $stream = new Stream($resource);
        $line = $stream->readLine();

        $this->assertInstanceOf(Str::class, $line);
        $this->assertSame("foo\n", $line->toString());
        $this->assertSame("bar\n", $stream->readLine()->toString());
        $this->assertSame('baz', $stream->readLine()->toString());
        $this->assertSame('', $stream->readLine()->toString());
    }

    public function testReadLineOnceClosed()
    {
        $resource = \tmpfile();
        \fwrite($resource, 'foobarbaz');
        $stream = new Stream($resource);
        $stream->close();

        $this->assertSame('', $stream->readLine()->toString());
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
        $stream->read();
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

    public function testStringCast()
    {
        $resource = \tmpfile();
        \fwrite($resource, 'foobarbaz');
        $stream = new Stream($resource);

        $this->assertSame('foobarbaz', $stream->toString());
    }

    public function testStringCastOnceClosed()
    {
        $resource = \tmpfile();
        \fwrite($resource, 'foobarbaz');
        $stream = new Stream($resource);
        $stream->close();

        $this->assertSame('', $stream->toString());
    }

    public function testOpen()
    {
        $file = \tempnam(\sys_get_temp_dir(), '');
        \file_put_contents($file, 'watev');

        $stream = Stream::open(Path::of($file));

        $this->assertInstanceOf(Stream::class, $stream);
        $this->assertSame('watev', $stream->toString());
    }

    public function testOfContent()
    {
        $stream = Stream::ofContent('foo');

        $this->assertInstanceOf(Stream::class, $stream);
        $this->assertSame('foo', $stream->toString());
    }

    /**
     * @dataProvider properties
     */
    public function testHoldProperty($property)
    {
        $this
            ->forAll(
                $property,
                Fixture::any(),
            )
            ->filter(static function($property, $stream) {
                return $property->applicableTo($stream);
            })
            ->then(static function($property, $stream) {
                $property->ensureHeldBy($stream);
            });
    }

    public function testHoldProperties()
    {
        $this
            ->forAll(
                PReadable::properties(),
                Fixture::any(),
            )
            ->then(static function($properties, $stream) {
                $properties->ensureHeldBy($stream);
            });
    }

    public function properties(): iterable
    {
        foreach (PReadable::list() as $property) {
            yield [$property];
        }
    }
}
