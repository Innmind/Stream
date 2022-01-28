<?php
declare(strict_types = 1);

namespace Innmind\Stream\Watch;

use Innmind\Stream\{
    Selectable,
    Readable,
    Writable,
};
use Innmind\Immutable\Set;

/**
 * @psalm-immutable
 */
final class Ready
{
    /** @var Set<Selectable&Readable> */
    private Set $read;
    /** @var Set<Selectable&Writable> */
    private Set $write;

    /**
     * @param Set<Selectable&Readable> $read
     * @param Set<Selectable&Writable> $write
     */
    public function __construct(Set $read, Set $write)
    {
        $this->read = $read;
        $this->write = $write;
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
}
