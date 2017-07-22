<?php
declare(strict_types = 1);

namespace Innmind\Stream\Writable;

use Innmind\Stream\{
    Stream as StreamInterface,
    Stream\Stream as Base,
    Writable,
    Selectable,
    Stream\Size,
    Stream\Position,
    Stream\Position\Mode,
    Exception\FailedToWriteToStream,
    Exception\DataPartiallyWritten
};
use Innmind\Immutable\Str;

final class Stream implements Writable, Selectable
{
    private $resource;
    private $stream;
    private $closed = false;

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

    public function write(Str $data): Writable
    {
        if ($this->closed()) {
            throw new FailedToWriteToStream;
        }

        $return = @fwrite($this->resource, (string) $data);

        if ($return === false) {
            throw new FailedToWriteToStream;
        }

        if ($return !== $data->length()) {
            throw new DataPartiallyWritten($data, $return);
        }

        return $this;
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
}
