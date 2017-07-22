<?php
declare(strict_types = 1);

namespace Innmind\Stream;

use Innmind\Immutable\Str;

interface Writable extends Stream
{
    public function write(Str $data): self;
}
