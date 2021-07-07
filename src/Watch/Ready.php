<?php
declare(strict_types = 1);

namespace Innmind\Stream\Watch;

use Innmind\Stream\{
    Selectable,
    Readable,
    Writable,
};
use Innmind\Immutable\Set;

final class Ready
{
    /** @var Set<Selectable&Readable> */
    private Set $read;
    /** @var Set<Selectable&Writable> */
    private Set $write;
    /** @var Set<Selectable> */
    private Set $outOfBand;

    /**
     * @param Set<Selectable&Readable> $read
     * @param Set<Selectable&Writable> $write
     * @param Set<Selectable> $outOfBand
     */
    public function __construct(Set $read, Set $write, Set $outOfBand)
    {
        $this->read = $read;
        $this->write = $write;
        $this->outOfBand = $outOfBand;
    }

    /**
     * @return Set<Selectable&Readable>
     */
    public function toRead(): Set
    {
        return $this->read;
    }

    /**
     * @return Set<Selectable&Writable>
     */
    public function toWrite(): Set
    {
        return $this->write;
    }

    /**
     * @return Set<Selectable>
     */
    public function toOutOfBand(): Set
    {
        return $this->outOfBand;
    }
}
