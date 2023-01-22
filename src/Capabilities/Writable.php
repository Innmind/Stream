<?php
declare(strict_types = 1);

namespace Innmind\Stream\Capabilities;

use Innmind\Stream\Writable as Write;
use Innmind\Url\Path;

interface Writable
{
    public function open(Path $path): Write;
}
