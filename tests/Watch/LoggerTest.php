<?php
declare(strict_types = 1);

namespace Tests\Innmind\Stream\Watch;

use Innmind\Stream\{
    Watch\Logger,
    Watch\Ready,
    Watch,
    Selectable,
};
use Innmind\Immutable\Set as ISet;
use Psr\Log\LoggerInterface;
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set,
};

class LoggerTest extends TestCase
{
    use BlackBox;

    public function testInterface()
    {
        $this->assertInstanceOf(
            Watch::class,
            new Logger(
                $this->createMock(Watch::class),
                $this->createMock(LoggerInterface::class),
            ),
        );
    }

    public function testWatch()
    {
        $streams = Set\FromGenerator::of(function() {
            while (true) {
                yield $this->createMock(Selectable::class);
            }
        });

        $this
            ->forAll(
                Set\Sequence::of($streams),
                Set\Sequence::of($streams),
                Set\Sequence::of($streams),
            )
            ->then(function($read, $write, $outOfBand) {
                $inner = $this->createMock(Watch::class);
                $inner
                    ->expects($this->once())
                    ->method('__invoke')
                    ->willReturn($expected = new Ready(
                        ISet::of(Selectable::class, ...$read),
                        ISet::of(Selectable::class, ...$write),
                        ISet::of(Selectable::class, ...$outOfBand),
                    ));
                $logger = $this->createMock(LoggerInterface::class);
                $logger
                    ->expects($this->once())
                    ->method('info')
                    ->with(
                        'Streams ready: {read} for read, {write} for write, {oob} for out of band',
                        [
                            'read' => \count($read),
                            'write' => \count($write),
                            'oob' => \count($outOfBand),
                        ],
                    );
                $watch = new Logger($inner, $logger);

                $this->assertSame($expected, $watch());
            });
    }

    public function testForRead()
    {
        $this
            ->forAll(Set\Sequence::of(
                Set\Elements::of($this->createMock(Selectable::class)),
                Set\Integers::between(1, 10),
            ))
            ->then(function($streams) {
                $inner = $this->createMock(Watch::class);
                $inner
                    ->expects($this->once())
                    ->method('forRead')
                    ->with(...$streams)
                    ->willReturn($inner2 = $this->createMock(Watch::class));
                $inner2
                    ->expects($this->once())
                    ->method('__invoke')
                    ->willReturn($expected = new Ready(
                        ISet::of(Selectable::class),
                        ISet::of(Selectable::class),
                        ISet::of(Selectable::class),
                    ));
                $logger = $this->createMock(LoggerInterface::class);
                $logger
                    ->expects($this->exactly(2))
                    ->method('info')
                    ->withConsecutive(
                        [
                            'Adding {count} streams to watch for read',
                            ['count' => \count($streams)],
                        ],
                        ['Streams ready: {read} for read, {write} for write, {oob} for out of band'],
                    );
                $watch = new Logger($inner, $logger);
                $watch2 = $watch->forRead(...$streams);

                $this->assertInstanceOf(Logger::class, $watch2);
                $this->assertNotSame($watch, $watch2);
                $this->assertSame($expected, $watch2());
            });
    }

    public function testForWrite()
    {
        $this
            ->forAll(Set\Sequence::of(
                Set\Elements::of($this->createMock(Selectable::class)),
                Set\Integers::between(1, 10),
            ))
            ->then(function($streams) {
                $inner = $this->createMock(Watch::class);
                $inner
                    ->expects($this->once())
                    ->method('forWrite')
                    ->with(...$streams)
                    ->willReturn($inner2 = $this->createMock(Watch::class));
                $inner2
                    ->expects($this->once())
                    ->method('__invoke')
                    ->willReturn($expected = new Ready(
                        ISet::of(Selectable::class),
                        ISet::of(Selectable::class),
                        ISet::of(Selectable::class),
                    ));
                $logger = $this->createMock(LoggerInterface::class);
                $logger
                    ->expects($this->exactly(2))
                    ->method('info')
                    ->withConsecutive(
                        [
                            'Adding {count} streams to watch for write',
                            ['count' => \count($streams)],
                        ],
                        ['Streams ready: {read} for read, {write} for write, {oob} for out of band'],
                    );
                $watch = new Logger($inner, $logger);
                $watch2 = $watch->forWrite(...$streams);

                $this->assertInstanceOf(Logger::class, $watch2);
                $this->assertNotSame($watch, $watch2);
                $this->assertSame($expected, $watch2());
            });
    }

    public function testForOutOfBand()
    {
        $this
            ->forAll(Set\Sequence::of(
                Set\Elements::of($this->createMock(Selectable::class)),
                Set\Integers::between(1, 10),
            ))
            ->then(function($streams) {
                $inner = $this->createMock(Watch::class);
                $inner
                    ->expects($this->once())
                    ->method('forOutOfBand')
                    ->with(...$streams)
                    ->willReturn($inner2 = $this->createMock(Watch::class));
                $inner2
                    ->expects($this->once())
                    ->method('__invoke')
                    ->willReturn($expected = new Ready(
                        ISet::of(Selectable::class),
                        ISet::of(Selectable::class),
                        ISet::of(Selectable::class),
                    ));
                $logger = $this->createMock(LoggerInterface::class);
                $logger
                    ->expects($this->exactly(2))
                    ->method('info')
                    ->withConsecutive(
                        [
                            'Adding {count} streams to watch for out of band',
                            ['count' => \count($streams)],
                        ],
                        ['Streams ready: {read} for read, {write} for write, {oob} for out of band'],
                    );
                $watch = new Logger($inner, $logger);
                $watch2 = $watch->forOutOfBand(...$streams);

                $this->assertInstanceOf(Logger::class, $watch2);
                $this->assertNotSame($watch, $watch2);
                $this->assertSame($expected, $watch2());
            });
    }

    public function testUnwatch()
    {
        $stream = $this->createMock(Selectable::class);
        $inner = $this->createMock(Watch::class);
        $inner
            ->expects($this->once())
            ->method('unwatch')
            ->with($stream)
            ->willReturn($inner2 = $this->createMock(Watch::class));
        $inner2
            ->expects($this->once())
            ->method('__invoke')
            ->willReturn($expected = new Ready(
                ISet::of(Selectable::class),
                ISet::of(Selectable::class),
                ISet::of(Selectable::class),
            ));
        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects($this->exactly(2))
            ->method('info')
            ->withConsecutive(
                ['Removing a stream from watch list'],
                ['Streams ready: {read} for read, {write} for write, {oob} for out of band'],
            );
        $watch = new Logger($inner, $logger);
        $watch2 = $watch->unwatch($stream);

        $this->assertInstanceOf(Logger::class, $watch2);
        $this->assertNotSame($watch, $watch2);
        $this->assertSame($expected, $watch2());
    }
}
