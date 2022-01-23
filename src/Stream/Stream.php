<?php
declare(strict_types = 1);

namespace Innmind\Stream\Stream;

use Innmind\Stream\{
    Stream as StreamInterface,
    Stream\Size,
    Stream\Position,
    Stream\Position\Mode,
    Exception\InvalidArgumentException,
    Exception\FailedToCloseStream,
    Exception\PositionNotSeekable,
};
use Innmind\Immutable\{
    Maybe,
    Either,
    SideEffect,
};

final class Stream implements StreamInterface
{
    /** @var resource */
    private $resource;

    /** @var Maybe<Size> */
    private Maybe $size;
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
        /** @var Maybe<Size> */
        $this->size = Maybe::nothing();

        $stats = \fstat($resource);

        if (isset($stats['size'])) {
            $this->size = Maybe::just(new Size((int) $stats['size']));
        }

        $meta = \stream_get_meta_data($resource);

        if ($meta['seekable'] && \substr($meta['uri'], 0, 9) !== 'php://std') {
            //stdin, stdout and stderr are not seekable
            $this->seekable = true;
            $this->rewind();
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

        $previous = $this->position();
        $mode ??= Mode::fromStart();

        $this->assertSeekable($position, $mode);

        $status = \fseek(
            $this->resource,
            $position->toInt(),
            $mode->toInt(),
        );

        if ($status === -1) {
            /** @psalm-suppress ImpureMethodCall */
            \fseek(
                $this->resource,
                $previous->toInt(),
                Mode::fromStart()->toInt(),
            );

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

    public function size(): Maybe
    {
        return $this->size;
    }

    public function close(): Either
    {
        if ($this->closed()) {
            return Either::right(new SideEffect);
        }

        /** @psalm-suppress InvalidPropertyAssignmentValue */
        $return = \fclose($this->resource);

        if ($return === false) {
            return Either::left(new FailedToCloseStream);
        }

        $this->closed = true;

        return Either::right(new SideEffect);
    }

    public function closed(): bool
    {
        /** @psalm-suppress DocblockTypeContradiction */
        return $this->closed || !\is_resource($this->resource);
    }

    /**
     * @throws PositionNotSeekable
     */
    private function assertSeekable(Position $position, Mode $mode): void
    {
        switch ($mode) {
            case Mode::fromCurrentPosition():
                $targetPosition = $this->position()->toInt() + $position->toInt();
                break;

            default: // fromStart
                $targetPosition = $position->toInt();
                break;
        }

        $assert = $this
            ->size()
            ->filter(static fn($size) => $targetPosition > $size->toInt())
            ->match(
                static fn() => static fn() => throw new PositionNotSeekable,
                static fn() => static fn() => null,
            );
        $assert();
    }
}
