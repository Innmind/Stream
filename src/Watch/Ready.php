<?php
declare(strict_types = 1);

namespace Innmind\Stream\Watch;

use Innmind\Stream\Selectable;
use Innmind\Immutable\SetInterface;
use function Innmind\Immutable\assertSet;

final class Ready
{
    private $read;
    private $write;
    private $outOfBand;

    public function __construct(
        SetInterface $read,
        SetInterface $write,
        SetInterface $outOfBand
    ) {
        assertSet(Selectable::class, $read, 1);
        assertSet(Selectable::class, $write, 2);
        assertSet(Selectable::class, $outOfBand, 3);

        $this->read = $read;
        $this->write = $write;
        $this->outOfBand = $outOfBand;
    }

    /**
     * @return SetInterface<Selectable>
     */
    public function toRead(): SetInterface
    {
        return $this->read;
    }

    /**
     * @return SetInterface<Selectable>
     */
    public function toWrite(): SetInterface
    {
        return $this->write;
    }

    /**
     * @return SetInterface<Selectable>
     */
    public function toOutOfBand(): SetInterface
    {
        return $this->outOfBand;
    }
}
