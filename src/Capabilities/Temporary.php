<?php
declare(strict_types = 1);

namespace Innmind\Stream\Capabilities;

use Innmind\Stream\{
    Bidirectional,
    Stream,
};

interface Temporary
{
    public function new(): Bidirectional;
}
