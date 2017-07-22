<?php
declare(strict_types = 1);

namespace Innmind\Stream;

use Innmind\Stream\Stream\{
    Position,
    Size,
    Position\Mode
};

interface Stream
{
    public function close(): self;
    public function closed(): bool;
    public function position(): Position;
    public function seek(Position $position, Mode $mode = null): self;
    public function rewind(): self;
    public function end(): bool;
    public function size(): Size;
    public function knowsSize(): bool;
}
