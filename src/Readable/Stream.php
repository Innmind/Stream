<?php
declare(strict_types = 1);

namespace Innmind\Stream\Readable;

use Innmind\Stream\{
    Stream as StreamInterface,
    Stream\Stream as Base,
    Readable,
    Selectable,
    Stream\Size,
    Stream\Position,
    Stream\Position\Mode
};
use Innmind\Url\Path;
use Innmind\Immutable\Str;

final class Stream implements Readable, Selectable
{
    /** @var resource */
    private $resource;
    private StreamInterface $stream;

    /**
     * @param resource $resource
     */
    public function __construct($resource)
    {
        $this->stream = new Base($resource);
        $this->resource = $resource;
    }

    public static function open(Path $path): self
    {
        return new self(\fopen($path->toString(), 'r'));
    }

    public static function ofContent(string $content): self
    {
        $resource = \fopen('php://temp', 'r+');
        \fwrite($resource, $content);

        return new self($resource);
    }

    /**
     * {@inheritdoc}
     */
    public function resource()
    {
        return $this->resource;
    }

    /**
     * {@inheritdoc}
     */
    public function read(int $length = null): Str
    {
        if ($this->closed()) {
            return Str::of('');
        }

        return Str::of((string) \stream_get_contents(
            $this->resource,
            $length ?? -1,
        ));
    }

    public function readLine(): Str
    {
        if ($this->closed()) {
            return Str::of('');
        }

        return Str::of((string) \fgets($this->resource));
    }

    public function position(): Position
    {
        return $this->stream->position();
    }

    public function seek(Position $position, Mode $mode = null): void
    {
        $this->stream->seek($position, $mode);
    }

    public function rewind(): void
    {
        $this->stream->rewind();
    }

    public function end(): bool
    {
        return $this->stream->end();
    }

    public function size(): Size
    {
        return $this->stream->size();
    }

    public function knowsSize(): bool
    {
        return $this->stream->knowsSize();
    }

    public function close(): void
    {
        $this->stream->close();
    }

    public function closed(): bool
    {
        return $this->stream->closed();
    }

    public function toString(): string
    {
        $this->rewind();

        return $this->read()->toString();
    }
}
