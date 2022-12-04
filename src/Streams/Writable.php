<?php
declare(strict_types = 1);

namespace Innmind\Stream\Streams;

use Innmind\Stream\Writable as Write;
use Innmind\Url\Path;

final class Writable
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

    public function open(Path $path): Write
    {
        return Write\Stream::of(\fopen($path->toString(), 'w'));
    }
}
