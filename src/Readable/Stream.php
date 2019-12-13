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
use Innmind\Immutable\Str;

final class Stream implements Readable, Selectable
{
    private $resource;
    private $stream;

    public function __construct($resource)
    {
        $this->stream = new Base($resource);
        $this->resource = $resource;
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

        return Str::of((string) stream_get_contents(
            $this->resource,
            $length ?? -1
        ));
    }

    public function readLine(): Str
    {
        if ($this->closed()) {
            return Str::of('');
        }

        return Str::of((string) fgets($this->resource));
    }

    public function position(): Position
    {
        return $this->stream->position();
    }

    public function seek(Position $position, Mode $mode = null): StreamInterface
    {
        $this->stream->seek($position, $mode);

        return $this;
    }

    public function rewind(): StreamInterface
    {
        $this->stream->rewind();

        return $this;
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

    public function close(): StreamInterface
    {
        $this->stream->close();

        return $this;
    }

    public function closed(): bool
    {
        return $this->stream->closed();
    }

    public function __toString(): string
    {
        $this->rewind();

        return $this->read()->toString();
    }
}
