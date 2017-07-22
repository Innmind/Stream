<?php
declare(strict_types = 1);

namespace Innmind\Stream\Stream;

use Innmind\Stream\{
    Stream as StreamInterface,
    Stream\Size,
    Stream\Position,
    Stream\Position\Mode,
    Exception\InvalidArgumentException,
    Exception\UnknownSize,
    Exception\FailedToCloseStream
};
use Innmind\Immutable\Str;

final class Stream implements StreamInterface
{
    private $resource;
    private $size;
    private $closed = false;

    public function __construct($resource)
    {
        if (!is_resource($resource) || get_resource_type($resource) !== 'stream') {
            throw new InvalidArgumentException;
        }

        $this->resource = $resource;
        $this->rewind();
        $stats = fstat($resource);

        if (isset($stats['size'])) {
            $this->size = new Size($stats['size']);
        }
    }

    public function position(): Position
    {
        return new Position(ftell($this->resource));
    }

    public function seek(Position $position, Mode $mode = null): StreamInterface
    {
        fseek(
            $this->resource,
            $position->toInt(),
            ($mode ?? Mode::fromStart())->toInt()
        );

        return $this;
    }

    public function rewind(): StreamInterface
    {
        $this->seek(new Position(0));

        return $this;
    }

    public function end(): bool
    {
        return feof($this->resource);
    }

    public function size(): Size
    {
        if (!$this->knowsSize()) {
            throw new UnknownSize;
        }

        return $this->size;
    }

    public function knowsSize(): bool
    {
        return $this->size instanceof Size;
    }

    public function close(): StreamInterface
    {
        $return = fclose($this->resource);

        if ($return = false) {
            throw new FailedToCloseStream;
        }

        $this->closed = true;

        return $this;
    }

    public function closed(): bool
    {
        return $this->closed;
    }
}
