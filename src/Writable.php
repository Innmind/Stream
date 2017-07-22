<?php
declare(strict_types = 1);

namespace Innmind\Stream;

interface Writable extends Stream
{
    public function write(Str $data): self;
}
