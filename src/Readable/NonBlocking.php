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
    Exception\NonBlockingModeNotSupported,
};
use Innmind\Immutable\{
    Str,
    Maybe,
    Either,
};

final class NonBlocking implements Readable, Selectable
{
    /** @var Readable&Selectable */
    private Readable $stream;

    private function __construct(Selectable $selectable)
    {
        $resource = $selectable->resource();
        $return = \stream_set_blocking($resource, false);

        if ($return === false) {
            throw new NonBlockingModeNotSupported;
        }

        $_ = \stream_set_write_buffer($resource, 0);
        $_ = \stream_set_read_buffer($resource, 0);

        if ($selectable instanceof Readable) {
            $this->stream = $selectable;
        } else {
            $this->stream = Stream::of($resource);
        }
    }

    public static function of(Selectable $selectable): self
    {
        return new self($selectable);
    }

    public function resource()
    {
        return $this->stream->resource();
    }

    public function read(int $length = null): Maybe
    {
        return $this->stream->read($length);
    }

    public function readLine(): Maybe
    {
        return $this->stream->readLine();
    }

    public function position(): Position
    {
        return $this->stream->position();
    }

    /** @psalm-suppress InvalidReturnType */
    public function seek(Position $position, Mode $mode = null): Either
    {
        /** @psalm-suppress InvalidReturnStatement */
        return $this->stream->seek($position, $mode)->map(fn() => $this);
    }

    /** @psalm-suppress InvalidReturnType */
    public function rewind(): Either
    {
        /** @psalm-suppress InvalidReturnStatement */
        return $this->stream->rewind()->map(fn() => $this);
    }

    public function end(): bool
    {
        return $this->stream->end();
    }

    public function size(): Maybe
    {
        return $this->stream->size();
    }

    public function close(): Either
    {
        return $this->stream->close();
    }

    public function closed(): bool
    {
        return $this->stream->closed();
    }

    public function toString(): Maybe
    {
        return $this->stream->toString();
    }
}
