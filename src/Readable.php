<?php
declare(strict_types = 1);

namespace Innmind\Stream;

use Innmind\Immutable\Str;

interface Readable extends Stream
{
    /**
     * @param int $length When omitted will read the remaining of the stream
     */
    public function read(int $length = null): Str;
    public function readLine(): Str;
    public function __toString(): string;
}
