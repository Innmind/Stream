<?php
declare(strict_types = 1);

namespace Innmind\Stream;

use Innmind\Stream\Stream\{
    Position,
    Size,
    Position\Mode
};
use Innmind\Immutable\Maybe;

interface Stream
{
    public function close(): void;
    public function closed(): bool;
    public function position(): Position;
    public function seek(Position $position, Mode $mode = null): void;
    public function rewind(): void;
    public function end(): bool;

    /**
     * @return Maybe<Size>
     */
    public function size(): Maybe;
}
