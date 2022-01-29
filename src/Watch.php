<?php
declare(strict_types = 1);

namespace Innmind\Stream;

use Innmind\Stream\Watch\Ready;
use Innmind\Immutable\Maybe;

interface Watch
{
    /**
     * @return Maybe<Ready> Returns nothing when it fails to lookup the streams
     */
    public function __invoke(): Maybe;

    /**
     * @psalm-mutation-free
     *
     * @param Selectable&Readable $read
     * @param Selectable&Readable $reads
     */
    public function forRead(Selectable $read, Selectable ...$reads): self;

    /**
     * @psalm-mutation-free
     *
     * @param Selectable&Writable $write
     * @param Selectable&Writable $writes
     */
    public function forWrite(Selectable $write, Selectable ...$writes): self;

    /**
     * @psalm-mutation-free
     */
    public function unwatch(Selectable $stream): self;
}
