<?php
declare(strict_types = 1);

namespace Innmind\Stream;

interface Selectable extends Stream
{
    /**
     * @psalm-mutation-free
     *
     * @return resource stream
     */
    public function resource();
}
