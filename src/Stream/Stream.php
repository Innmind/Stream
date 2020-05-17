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
    Exception\FailedToCloseStream,
    Exception\PositionNotSeekable,
};

final class Stream implements StreamInterface
{
    /** @var resource */
    private $resource;
    private ?Size $size = null;
    private bool $closed = false;
    private bool $seekable = false;

    /**
     * @param resource $resource
     */
    public function __construct($resource)
    {
        /**
         * @psalm-suppress DocblockTypeContradiction
         * @psalm-suppress RedundantConditionGivenDocblockType
         */
        if (!\is_resource($resource) || \get_resource_type($resource) !== 'stream') {
            throw new InvalidArgumentException;
        }

        $this->resource = $resource;
        $meta = \stream_get_meta_data($resource);

        if ($meta['seekable'] && \substr($meta['uri'], 0, 9) !== 'php://std') {
            //stdin, stdout and stderr are not seekable
            $this->seekable = true;
            $this->rewind();
        }

        $stats = \fstat($resource);

        if (isset($stats['size'])) {
            $this->size = new Size((int) $stats['size']);
        }
    }

    public function position(): Position
    {
        if ($this->closed()) {
            return new Position(0);
        }

        return new Position(\ftell($this->resource));
    }

    public function seek(Position $position, Mode $mode = null): void
    {
        if (!$this->seekable) {
            throw new PositionNotSeekable;
        }

        if ($this->closed()) {
            return;
        }

        $status = \fseek(
            $this->resource,
            $position->toInt(),
            ($mode ?? Mode::fromStart())->toInt(),
        );

        if ($status === -1) {
            throw new PositionNotSeekable;
        }
    }

    public function rewind(): void
    {
        $this->seek(new Position(0));
    }

    public function end(): bool
    {
        if ($this->closed()) {
            return true;
        }

        return \feof($this->resource);
    }

    public function size(): Size
    {
        if (!$this->size instanceof Size) {
            throw new UnknownSize;
        }

        return $this->size;
    }

    public function knowsSize(): bool
    {
        return $this->size instanceof Size;
    }

    public function close(): void
    {
        if ($this->closed()) {
            return;
        }

        /** @psalm-suppress InvalidPropertyAssignmentValue */
        $return = \fclose($this->resource);

        if ($return === false) {
            throw new FailedToCloseStream;
        }

        $this->closed = true;
    }

    public function closed(): bool
    {
        /** @psalm-suppress DocblockTypeContradiction */
        return $this->closed || !\is_resource($this->resource);
    }
}
