<?php
declare(strict_types = 1);

namespace Innmind\Stream\Watch;

use Innmind\Stream\Selectable;
use Innmind\Immutable\Set;
use function Innmind\Immutable\assertSet;

final class Ready
{
    private Set $read;
    private Set $write;
    private Set $outOfBand;

    public function __construct(
        Set $read,
        Set $write,
        Set $outOfBand
    ) {
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
