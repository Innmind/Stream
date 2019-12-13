<?php
declare(strict_types = 1);

namespace Innmind\Stream;

use Innmind\TimeContinuum\ElapsedPeriod;
use Innmind\Immutable\{
    Map,
    Set,
};

/**
 * @deprecated Use Watch\Select instead
 */
final class Select
{
    private $select;

    public function __construct(ElapsedPeriod $timeout)
    {
        $this->select = new Watch\Select($timeout);
    }

    public function forRead(Selectable $read, Selectable ...$reads): self
    {
        $self = clone $this;
        $self->select = $self->select->forRead($read, ...$reads);

        return $self;
    }

    public function forWrite(Selectable $write, Selectable ...$writes): self
    {
        $self = clone $this;
        $self->select = $self->select->forWrite($write, ...$writes);

        return $self;
    }

    public function forOutOfBand(
        Selectable $outOfBand,
        Selectable ...$outOfBands
    ): self {
        $self = clone $this;
        $self->select = $self->select->forOutOfBand($outOfBand, ...$outOfBands);

        return $self;
    }

    public function unwatch(Selectable $stream): self
    {
        $self = clone $this;
        $self->select = $self->select->unwatch($stream);

        return $self;
    }

    /**
     * @return Map<string, Set<Selectable>> Key can be read, write or out_of_band
     */
    public function __invoke(): Map
    {
        $ready = ($this->select)();

        return Map::of('string', Set::class)
            ('read', $ready->toRead())
            ('write', $ready->toWrite())
            ('out_of_band', $ready->toOutOfBand());
    }
}
