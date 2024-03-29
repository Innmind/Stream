<?php
declare(strict_types = 1);

namespace Innmind\Stream\Streams;

use Innmind\Stream\{
    Capabilities,
    Bidirectional,
    Stream,
};

final class Temporary implements Capabilities\Temporary
{
    private function __construct()
    {
    }

    /**
     * @internal
     */
    public static function of(): self
    {
        return new self;
    }

    public function new(): Bidirectional
    {
        return Stream\Bidirectional::of(\fopen('php://temp', 'r+'));
    }
}
