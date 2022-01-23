<?php
declare(strict_types = 1);

namespace Innmind\Stream;

use Innmind\Stream\Watch\Ready;
use Innmind\Immutable\Maybe;

interface Watch
{
    /**
     * @return Maybe<Ready> Returns nothing when it fails to loojup the streams
     */
    public function __invoke(): Maybe;

    /**
     * @param Selectable&Readable $read
     * @param Selectable&Readable $reads
     */
    public function forRead(Selectable $read, Selectable ...$reads): self;

    /**
     * @param Selectable&Writable $write
     * @param Selectable&Writable $writes
     */
    public function forWrite(Selectable $write, Selectable ...$writes): self;
    public function forOutOfBand(Selectable $outOfBand, Selectable ...$outOfBands): self;
    public function unwatch(Selectable $stream): self;
}
