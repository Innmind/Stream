<?php
declare(strict_types = 1);

namespace Innmind\Stream\Stream;

use Innmind\Stream\{
    Readable,
    Writable,
    Bidirectional as BidirectionalInterface,
    Selectable,
    Stream as StreamInterface,
    Stream\Position\Mode,
};
use Innmind\Immutable\{
    Str,
    Maybe,
    Either,
    SideEffect,
};

final class Bidirectional implements BidirectionalInterface, Selectable
{
    private Readable $read;
    private Writable $write;
    /** @var resource */
    private $resource;

    /**
     * @param resource $resource
     */
    public function __construct($resource)
    {
        $this->read = new Readable\NonBlocking(
            new Readable\Stream($resource),
        );
        $this->write = new Writable\Stream($resource);
        $this->resource = $resource;
    }

    public function resource()
    {
        return $this->resource;
    }

    public function close(): Either
    {
        return $this->write->close();
    }

    public function closed(): bool
    {
        return $this->write->closed();
    }

    public function position(): Position
    {
        return $this->read->position();
    }

    /** @psalm-suppress InvalidReturnType */
    public function seek(Position $position, Mode $mode = null): Either
    {
        /** @psalm-suppress InvalidReturnStatement */
        return $this->read->seek($position, $mode)->map(fn() => $this);
    }

    /** @psalm-suppress InvalidReturnType */
    public function rewind(): Either
    {
        /** @psalm-suppress InvalidReturnStatement */
        return $this->read->rewind()->map(fn() => $this);
    }

    public function end(): bool
    {
        return $this->read->end();
    }

    public function size(): Maybe
    {
        return $this->read->size();
    }

    public function read(int $length = null): Maybe
    {
        return $this->read->read($length);
    }

    public function readLine(): Maybe
    {
        return $this->read->readLine();
    }

    /** @psalm-suppress InvalidReturnType */
    public function write(Str $data): Either
    {
        /** @psalm-suppress InvalidReturnStatement */
        return $this->write->write($data)->map(fn() => $this);
    }

    public function toString(): Maybe
    {
        return $this->read->toString();
    }
}
