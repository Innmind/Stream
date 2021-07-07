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
use Innmind\Immutable\{
    Str,
    Maybe,
};

final class Stream implements Writable, Selectable
{
    /** @var resource */
    private $resource;
    private StreamInterface $stream;
    private bool $closed = false;

    /**
     * @param resource $resource
     */
    public function __construct($resource)
    {
        $this->stream = new Base($resource);
        $this->resource = $resource;
    }

    public function resource()
    {
        return $this->resource;
    }

    public function write(Str $data): void
    {
        if ($this->closed()) {
            throw new FailedToWriteToStream;
        }

        $written = @\fwrite($this->resource, $data->toString());

        if ($written === false) {
            throw new FailedToWriteToStream;
        }

        if ($written !== $data->length()) {
            throw new DataPartiallyWritten($data, $written);
        }
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

    public function size(): Maybe
    {
        return $this->stream->size();
    }

    public function close(): void
    {
        $this->stream->close();
    }

    public function closed(): bool
    {
        return $this->stream->closed();
    }
}
