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
use Innmind\Immutable\Str;

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

    /**
     * {@inheritdoc}
     */
    public function resource()
    {
        return $this->resource;
    }

    public function close(): void
    {
        $this->write->close();
    }

    public function closed(): bool
    {
        return $this->write->closed();
    }

    public function position(): Position
    {
        return $this->read->position();
    }

    public function seek(Position $position, Mode $mode = null): void
    {
        $this->read->seek($position, $mode);
    }

    public function rewind(): void
    {
        $this->read->rewind();
    }

    public function end(): bool
    {
        return $this->read->end();
    }

    public function size(): Size
    {
        return $this->read->size();
    }

    public function knowsSize(): bool
    {
        return $this->read->knowsSize();
    }

    /**
     * {@inheritdoc}
     */
    public function read(int $length = null): Str
    {
        return $this->read->read($length);
    }

    public function readLine(): Str
    {
        return $this->read->readLine();
    }

    public function write(Str $data): void
    {
        $this->write->write($data);
    }

    public function toString(): string
    {
        return $this->read->toString();
    }
}
