<?php
declare(strict_types = 1);

namespace Innmind\Stream\Watch;

use Innmind\Stream\{
    Watch,
    Selectable,
};
use Innmind\Immutable\Maybe;
use Psr\Log\LoggerInterface;

final class Logger implements Watch
{
    private Watch $watch;
    private LoggerInterface $logger;

    public function __construct(Watch $watch, LoggerInterface $logger)
    {
        $this->watch = $watch;
        $this->logger = $logger;
    }

    public function __invoke(): Maybe
    {
        return ($this->watch)()->map(fn($ready) => $this->log($ready));
    }

    /**
     * @psalm-mutation-free
     */
    public function forRead(Selectable $read, Selectable ...$reads): Watch
    {
        /** @psalm-suppress ImpureMethodCall */
        $this->logger->info(
            'Adding {count} streams to watch for read',
            ['count' => \count($reads) + 1],
        );

        return new self(
            $this->watch->forRead($read, ...$reads),
            $this->logger,
        );
    }

    /**
     * @psalm-mutation-free
     */
    public function forWrite(Selectable $write, Selectable ...$writes): Watch
    {
        /** @psalm-suppress ImpureMethodCall */
        $this->logger->info(
            'Adding {count} streams to watch for write',
            ['count' => \count($writes) + 1],
        );

        return new self(
            $this->watch->forWrite($write, ...$writes),
            $this->logger,
        );
    }

    /**
     * @psalm-mutation-free
     */
    public function unwatch(Selectable $stream): Watch
    {
        /** @psalm-suppress ImpureMethodCall */
        $this->logger->info('Removing a stream from watch list');

        return new self(
            $this->watch->unwatch($stream),
            $this->logger,
        );
    }

    private function log(Ready $ready): Ready
    {
        $this->logger->info(
            'Streams ready: {read} for read, {write} for write',
            [
                'read' => $ready->toRead()->size(),
                'write' => $ready->toWrite()->size(),
            ],
        );

        return $ready;
    }
}
