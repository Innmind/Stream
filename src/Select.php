<?php
declare(strict_types = 1);

namespace Innmind\Stream;

use Innmind\Stream\Exception\SelectFailed;
use Innmind\TimeContinuum\ElapsedPeriod;
use Innmind\Immutable\{
    MapInterface,
    Map,
    SetInterface,
    Set
};

final class Select
{
    private $timeout;
    private $read;
    private $write;
    private $outOfBand;

    public function __construct(ElapsedPeriod $timeout)
    {
        $this->timeout = $timeout;
        $this->read = new Map('resource', Selectable::class);
        $this->write = new Map('resource', Selectable::class);
        $this->outOfBand = new Map('resource', Selectable::class);
    }

    public function forRead(Selectable $read, Selectable ...$reads): self
    {
        $self = clone $this;
        $self->read = $self->read->put(
            $read->resource(),
            $read
        );

        foreach ($reads as $read) {
            $self->read = $self->read->put(
                $read->resource(),
                $read
            );
        }

        return $self;
    }

    public function forWrite(Selectable $write, Selectable ...$writes): self
    {
        $self = clone $this;
        $self->write = $self->write->put(
            $write->resource(),
            $write
        );

        foreach ($writes as $write) {
            $self->write = $self->write->put(
                $write->resource(),
                $write
            );
        }

        return $self;
    }

    public function forOutOfBand(
        Selectable $outOfBand,
        Selectable ...$outOfBands
    ): self {
        $self = clone $this;
        $self->outOfBand = $self->outOfBand->put(
            $outOfBand->resource(),
            $outOfBand
        );

        foreach ($outOfBands as $outOfBand) {
            $self->outOfBand = $self->outOfBand->put(
                $outOfBand->resource(),
                $outOfBand
            );
        }

        return $self;
    }

    public function unwatch(Selectable $stream): self
    {
        $resource = $stream->resource();
        $self = clone $this;
        $self->read = $self->read->remove($resource);
        $self->write = $self->write->remove($resource);
        $self->outOfBand = $self->outOfBand->remove($resource);

        return $self;
    }

    /**
     * @return MapInterface<string, SetInterface<Selectable>> Key can be read, write or out_of_band
     */
    public function __invoke(): MapInterface
    {
        if (
            $this->read->size() === 0 &&
            $this->write->size() === 0 &&
            $this->outOfBand->size() === 0
        ) {
            return (new Map('string', SetInterface::class))
                ->put('read', new Set(Selectable::class))
                ->put('write', new Set(Selectable::class))
                ->put('out_of_band', new Set(Selectable::class));
        }

        $read = $this->read->keys()->toPrimitive();
        $write = $this->write->keys()->toPrimitive();
        $outOfBand = $this->outOfBand->keys()->toPrimitive();
        $seconds = (int) ($this->timeout->milliseconds() / 1000);
        $microseconds = ($this->timeout->milliseconds() - ($seconds * 1000)) * 1000;

        $return = @stream_select(
            $read,
            $write,
            $outOfBand,
            $seconds,
            $microseconds
        );

        if ($return === false) {
            $error = error_get_last();

            throw new SelectFailed(
                $error['message'],
                $error['type']
            );
        }

        return (new Map('string', SetInterface::class))
            ->put(
                'read',
                array_reduce(
                    $read,
                    function(SetInterface $carry, $resource): SetInterface {
                        return $carry->add(
                            $this->read->get($resource)
                        );
                    },
                    new Set(Selectable::class)
                )
            )
            ->put(
                'write',
                array_reduce(
                    $write,
                    function(SetInterface $carry, $resource): SetInterface {
                        return $carry->add(
                            $this->write->get($resource)
                        );
                    },
                    new Set(Selectable::class)
                )
            )
            ->put(
                'out_of_band',
                array_reduce(
                    $outOfBand,
                    function(SetInterface $carry, $resource): SetInterface {
                        return $carry->add(
                            $this->outOfBand->get($resource)
                        );
                    },
                    new Set(Selectable::class)
                )
            );
    }
}
