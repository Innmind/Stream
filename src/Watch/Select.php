<?php
declare(strict_types = 1);

namespace Innmind\Stream\Watch;

use Innmind\Stream\{
    Watch,
    Selectable,
    Exception\SelectFailed
};
use Innmind\TimeContinuum\ElapsedPeriod;
use Innmind\Immutable\{
    Map,
    Set,
};

final class Select implements Watch
{
    private ElapsedPeriod $timeout;
    /** @var Map<resource, Selectable> */
    private Map $read;
    /** @var Map<resource, Selectable> */
    private Map $write;
    /** @var Map<resource, Selectable> */
    private Map $outOfBand;
    /** @var list<resource> */
    private array $readResources;
    /** @var list<resource> */
    private array $writeResources;
    /** @var list<resource> */
    private array $outOfBandResources;

    public function __construct(ElapsedPeriod $timeout)
    {
        $this->timeout = $timeout;
        /** @var Map<resource, Selectable> */
        $this->read = Map::of('resource', Selectable::class);
        /** @var Map<resource, Selectable> */
        $this->write = Map::of('resource', Selectable::class);
        /** @var Map<resource, Selectable> */
        $this->outOfBand = Map::of('resource', Selectable::class);
        $this->readResources = [];
        $this->writeResources = [];
        $this->outOfBandResources = [];
    }

    public function __invoke(): Ready
    {
        if (
            $this->read->empty() &&
            $this->write->empty() &&
            $this->outOfBand->empty()
        ) {
            /** @var Set<Selectable> */
            $nothingReady = Set::of(Selectable::class);

            return new Ready($nothingReady, $nothingReady, $nothingReady);
        }

        $read = $this->readResources;
        $write = $this->writeResources;
        $outOfBand = $this->outOfBandResources;
        $seconds = (int) ($this->timeout->milliseconds() / 1000);
        $microseconds = ($this->timeout->milliseconds() - ($seconds * 1000)) * 1000;

        $return = @\stream_select(
            $read,
            $write,
            $outOfBand,
            $seconds,
            $microseconds,
        );

        if ($return === false) {
            $error = \error_get_last();

            /**
             * @psalm-suppress PossiblyNullArrayAccess
             * @psalm-suppress PossiblyNullArgument
             */
            throw new SelectFailed(
                $error['message'],
                $error['type'],
            );
        }

        /**
         * @var Set<Selectable>
         * @psalm-suppress MissingClosureParamType
         * @psalm-suppress PossiblyNullArgument
         * @psalm-suppress MixedArgument because $resource can't be typed
         */
        $readable = \array_reduce(
            $read,
            function(Set $carry, $resource): Set {
                return $carry->add(
                    $this->read->get($resource),
                );
            },
            Set::of(Selectable::class),
        );
        /**
         * @var Set<Selectable>
         * @psalm-suppress MissingClosureParamType
         * @psalm-suppress PossiblyNullArgument
         * @psalm-suppress MixedArgument because $resource can't be typed
         */
        $writable = \array_reduce(
            $write,
            function(Set $carry, $resource): Set {
                return $carry->add(
                    $this->write->get($resource),
                );
            },
            Set::of(Selectable::class),
        );
        /**
         * @var Set<Selectable>
         * @psalm-suppress MissingClosureParamType
         * @psalm-suppress PossiblyNullArgument
         * @psalm-suppress MixedArgument because $resource can't be typed
         */
        $outOfBandReady = \array_reduce(
            $outOfBand,
            function(Set $carry, $resource): Set {
                return $carry->add(
                    $this->outOfBand->get($resource),
                );
            },
            Set::of(Selectable::class),
        );

        return new Ready($readable, $writable, $outOfBandReady);
    }

    public function forRead(Selectable $read, Selectable ...$reads): Watch
    {
        $self = clone $this;
        $self->read = ($self->read)(
            $read->resource(),
            $read,
        );
        $self->readResources[] = $read->resource();

        foreach ($reads as $read) {
            $self->read = ($self->read)(
                $read->resource(),
                $read,
            );
            $self->readResources[] = $read->resource();
        }

        return $self;
    }

    public function forWrite(Selectable $write, Selectable ...$writes): Watch
    {
        $self = clone $this;
        $self->write = ($self->write)(
            $write->resource(),
            $write,
        );
        $self->writeResources[] = $write->resource();

        foreach ($writes as $write) {
            $self->write = ($self->write)(
                $write->resource(),
                $write,
            );
            $self->writeResources[] = $write->resource();
        }

        return $self;
    }

    public function forOutOfBand(
        Selectable $outOfBand,
        Selectable ...$outOfBands
    ): Watch {
        $self = clone $this;
        $self->outOfBand = ($self->outOfBand)(
            $outOfBand->resource(),
            $outOfBand,
        );
        $self->outOfBandResources[] = $outOfBand->resource();

        foreach ($outOfBands as $outOfBand) {
            $self->outOfBand = ($self->outOfBand)(
                $outOfBand->resource(),
                $outOfBand,
            );
            $self->outOfBandResources[] = $outOfBand->resource();
        }

        return $self;
    }

    public function unwatch(Selectable $stream): Watch
    {
        $resource = $stream->resource();
        $self = clone $this;
        $self->read = $self->read->remove($resource);
        $self->write = $self->write->remove($resource);
        $self->outOfBand = $self->outOfBand->remove($resource);
        /** @var list<resource> */
        $self->readResources = \array_values(\array_filter(
            $self->readResources,
            static function($read) use ($resource): bool {
                return $read !== $resource;
            },
        ));
        /** @var list<resource> */
        $self->writeResources = \array_values(\array_filter(
            $self->writeResources,
            static function($write) use ($resource): bool {
                return $write !== $resource;
            },
        ));
        /** @var list<resource> */
        $self->outOfBandResources = \array_values(\array_filter(
            $self->outOfBandResources,
            static function($outOfBand) use ($resource): bool {
                return $outOfBand !== $resource;
            },
        ));

        return $self;
    }
}
