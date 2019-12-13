<?php
declare(strict_types = 1);

namespace Innmind\Stream\Stream;

use Innmind\Stream\Exception\PositionCantBeNegative;

final class Position
{
    private int $value;

    public function __construct(int $value)
    {
        if ($value < 0) {
            throw new PositionCantBeNegative((string) $value);
        }

        $this->value = $value;
    }

    public function toInt(): int
    {
        return $this->value;
    }
}
