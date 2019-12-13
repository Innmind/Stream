<?php
declare(strict_types = 1);

namespace Innmind\Stream\Watch;

use Innmind\Stream\Selectable;
use Innmind\Immutable\Set;
use function Innmind\Immutable\assertSet;

final class Ready
{
    /** @var Set<Selectable> */
    private Set $read;
    /** @var Set<Selectable> */
    private Set $write;
    /** @var Set<Selectable> */
    private Set $outOfBand;

    /**
     * @param Set<Selectable> $read
     * @param Set<Selectable> $write
     * @param Set<Selectable> $outOfBand
     */
    public function __construct(Set $read, Set $write, Set $outOfBand)
    {
        assertSet(Selectable::class, $read, 1);
        assertSet(Selectable::class, $write, 2);
        assertSet(Selectable::class, $outOfBand, 3);

        $this->read = $read;
        $this->write = $write;
        $this->outOfBand = $outOfBand;
    }

    /**
     * @return Set<Selectable>
     */
    public function toRead(): Set
    {
        return $this->read;
    }

    /**
     * @return Set<Selectable>
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
