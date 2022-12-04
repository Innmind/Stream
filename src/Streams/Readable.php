<?php
declare(strict_types = 1);

namespace Innmind\Stream\Streams;

use Innmind\Stream\Readable as Read;
use Innmind\Url\Path;

final class Readable
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

    public function open(Path $path): Read
    {
        return Read\Stream::open($path);
    }
}
