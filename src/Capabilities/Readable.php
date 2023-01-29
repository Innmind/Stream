<?php
declare(strict_types = 1);

namespace Innmind\Stream\Capabilities;

use Innmind\Stream\Readable as Read;
use Innmind\Url\Path;

interface Readable
{
    public function open(Path $path): Read;

    /**
     * @param resource $resource
     */
    public function acquire($resource): Read;
}
