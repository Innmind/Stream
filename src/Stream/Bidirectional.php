<?php
declare(strict_types = 1);

namespace Innmind\Stream\Stream;

use Innmind\Stream\{
    Readable,
    Writable,
    Selectable,
    Stream as StreamInterface,
    Stream\Position,
    Stream\Size,
    Stream\Position\Mode
};
use Innmind\Immutable\Str;

final class Bidirectional implements Readable, Writable, Selectable
{
    private $read;
    private $write;
    private $resource;

    public function __construct($resource)
    {
        $this->read = new Readable\NonBlocking(
            new Readable\Stream($resource)
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

    public function close(): StreamInterface
    {
        $this->write->close();

        return $this;
    }

    public function closed(): bool
    {
        return $this->write->closed();
    }

    public function position(): Position
    {
        return $this->read->position();
    }

    public function seek(Position $position, Mode $mode = null): StreamInterface
    {
        $this->read->seek($position, $mode);

        return $this;
    }

    public function rewind(): StreamInterface
    {
        $this->read->rewind();

        return $this;
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

    public function write(Str $data): Writable
    {
        $this->write->write($data);

        return $this;
    }

    public function __toString(): string
    {
        return (string) $this->read;
    }
}