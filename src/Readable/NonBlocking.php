<?php
declare(strict_types = 1);

namespace Innmind\Stream\Readable;

use Innmind\Stream\{
    Stream as StreamInterface,
    Readable,
    Selectable,
    Stream\Position,
    Stream\Size,
    Stream\Position\Mode,
    Exception\NonBlockingModeNotSupported
};
use Innmind\Immutable\Str;

final class NonBlocking implements Readable, Selectable
{
    private $stream;

    public function __construct(Selectable $selectable)
    {
        $resource = $selectable->resource();
        $return = stream_set_blocking($resource, false);

        if ($return === false) {
            throw new NonBlockingModeNotSupported;
        }

        stream_set_write_buffer($resource, 0);
        stream_set_read_buffer($resource, 0);

        if ($selectable instanceof Readable) {
            $this->stream = $selectable;
        } else {
            $this->stream = new Stream($resource);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function resource()
    {
        return $this->stream->resource();
    }

    /**
     * {@inheritdoc}
     */
    public function read(int $length = null): Str
    {
        return $this->stream->read($length);
    }

    public function readLine(): Str
    {
        return $this->stream->readLine();
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
        return $this->stream->toString();
    }
}
