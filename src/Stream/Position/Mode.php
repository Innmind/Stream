<?php
declare(strict_types = 1);

namespace Innmind\Stream\Stream\Position;

final class Mode
{
    private static $set;
    private static $cur;

    private $value;

    private function __construct(int $value)
    {
        $this->value = $value;
    }

    public static function fromStart(): self
    {
        return self::$set ?? self::$set = new self(SEEK_SET);
    }

    public static function fromCurrentPosition(): self
    {
        return self::$cur ?? self::$cur = new self(SEEK_CUR);
    }

    public function toInt(): int
    {
        return $this->value;
    }
}
