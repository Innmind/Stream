<?php
declare(strict_types = 1);

namespace Innmind\Stream;

use Innmind\Stream\{
    Stream\Position,
    Stream\Size,
    Stream\Position\Mode,
    Exception\FailedToCloseStream,
    Exception\PositionNotSeekable,
};
use Innmind\Immutable\{
    Maybe,
    Either,
    SideEffect,
};

interface Stream
{
    /**
     * It returns a SideEffect instead of the stream on the right hand size
     * because you should no longer use the stream once it's closed
     *
     * @return Either<FailedToCloseStream, SideEffect>
     */
    public function close(): Either;
    public function closed(): bool;
    public function position(): Position;

    /**
     * @return Either<PositionNotSeekable, self>
     */
    public function seek(Position $position, Mode $mode = null): Either;

    /**
     * @return Either<PositionNotSeekable, self>
     */
    public function rewind(): Either;
    public function end(): bool;

    /**
     * @return Maybe<Size>
     */
    public function size(): Maybe;
}
