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
    DataPartiallyWritten,
    FailedToWriteToStream,
};
use Innmind\Immutable\{
    Str,
    Maybe,
    Either,
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

    public function write(Str $data): Either
    {
        if ($this->closed()) {
            /** @var Either<FailedToWriteToStream|DataPartiallyWritten, Writable> */
            return Either::left(new FailedToWriteToStream);
        }

        $written = @\fwrite($this->resource, $data->toString());

        if ($written === false) {
            /** @var Either<FailedToWriteToStream|DataPartiallyWritten, Writable> */
            return Either::left(new FailedToWriteToStream);
        }

        if ($written !== $data->length()) {
            /** @var Either<FailedToWriteToStream|DataPartiallyWritten, Writable> */
            return Either::left(new DataPartiallyWritten($data, $written));
        }

        /** @var Either<FailedToWriteToStream|DataPartiallyWritten, Writable> */
        return Either::right($this);
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
}
