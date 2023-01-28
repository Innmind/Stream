<?php
declare(strict_types = 1);

namespace Tests\Innmind\Stream\Watch;

use Innmind\Stream\{
    Watch\Select,
    Watch\Ready,
    Watch,
    Readable,
    Writable,
};
use Innmind\TimeContinuum\Earth\ElapsedPeriod;
use Symfony\Component\Process\Process;
use PHPUnit\Framework\TestCase;

class SelectTest extends TestCase
{
    private $read;
    private $write;

    public function setUp(): void
    {
        $this->read = new Process(['php', 'fixtures/read.php']);
        $this->write = new Process(['php', 'fixtures/write.php']);
        $this->read = $this->read->start();
        $this->write = $this->write->start();
        \sleep(1);
    }

    public function tearDown(): void
    {
        try {
            $this->read->stop(10, \SIGKILL);
            $this->write->stop(10, \SIGKILL);
        } catch (\Throwable $e) {
            //pass
        }
    }

    public function testInterface()
    {
        $this->assertInstanceOf(
            Watch::class,
            Select::timeoutAfter(new ElapsedPeriod(0)),
        );
    }

    public function testForRead()
    {
        $select = Select::timeoutAfter(new ElapsedPeriod(0));
        $resource = \fopen('php://temp', 'w');
        $stream = $this->createMock(Readable::class);
        $stream
            ->expects($this->exactly(2))
            ->method('resource')
            ->willReturn($resource);

        $select2 = $select->forRead($stream);

        $this->assertInstanceOf(Select::class, $select2);
        $this->assertNotSame($select2, $select);
    }

    public function testForWrite()
    {
        $select = Select::timeoutAfter(new ElapsedPeriod(0));
        $resource = \fopen('php://temp', 'w');
        $stream = $this->createMock(Writable::class);
        $stream
            ->expects($this->exactly(2))
            ->method('resource')
            ->willReturn($resource);

        $select2 = $select->forWrite($stream);

        $this->assertInstanceOf(Select::class, $select2);
        $this->assertNotSame($select2, $select);
    }

    public function testInvokeWhenNoStream()
    {
        $ready = Select::timeoutAfter(new ElapsedPeriod(0))()->match(
            static fn($ready) => $ready,
            static fn() => null,
        );

        $this->assertInstanceOf(Ready::class, $ready);
        $this->assertCount(0, $ready->toRead());
        $this->assertCount(0, $ready->toWrite());
    }

    public function testInvoke()
    {
        $read = $this->createMock(Readable::class);
        $read
            ->expects($this->exactly(2))
            ->method('resource')
            ->willReturn($readSocket = \stream_socket_client('unix:///tmp/read.sock'));
        $write = $this->createMock(Writable::class);
        $write
            ->expects($this->exactly(2))
            ->method('resource')
            ->willReturn($writeSocket = \stream_socket_client('unix:///tmp/write.sock'));
        $select = Select::timeoutAfter(new ElapsedPeriod(0))
            ->forRead($read)
            ->forWrite($write);
        \fwrite($readSocket, 'foo');
        \fwrite($writeSocket, 'foo');

        $ready = $select()->match(
            static fn($ready) => $ready,
            static fn() => null,
        );

        $this->assertInstanceOf(Ready::class, $ready);
        $this->assertCount(1, $ready->toRead());
        $this->assertCount(1, $ready->toWrite());
        $this->assertSame($read, $ready->toRead()->find(static fn() => true)->match(
            static fn($stream) => $stream,
            static fn() => null,
        ));
        $this->assertSame($write, $ready->toWrite()->find(static fn() => true)->match(
            static fn($stream) => $stream,
            static fn() => null,
        ));

        $ready = $select()->match(
            static fn($ready) => $ready,
            static fn() => null,
        );

        $this->assertCount(1, $ready->toRead());
        $this->assertCount(1, $ready->toWrite());
    }

    public function testUnwatch()
    {
        $read = $this->createMock(Readable::class);
        $read
            ->expects($this->exactly(3))
            ->method('resource')
            ->willReturn($readSocket = \stream_socket_client('unix:///tmp/read.sock'));
        $write = $this->createMock(Writable::class);
        $write
            ->expects($this->exactly(3))
            ->method('resource')
            ->willReturn($writeSocket = \stream_socket_client('unix:///tmp/write.sock'));
        $select = Select::timeoutAfter(new ElapsedPeriod(0))
            ->forRead($read)
            ->forWrite($write);
        \fwrite($readSocket, 'foo');
        \fwrite($writeSocket, 'foo');

        $select();
        $select2 = $select
            ->unwatch($read)
            ->unwatch($write);

        $this->assertInstanceOf(Select::class, $select);
        $this->assertNotSame($select2, $select);

        $streams = $select2()->match(
            static fn($ready) => $ready,
            static fn() => null,
        );

        $this->assertCount(0, $streams->toRead());
        $this->assertCount(0, $streams->toWrite());
    }

    public function testWaitForever()
    {
        $read = $this->createMock(Readable::class);
        $read
            ->expects($this->exactly(2))
            ->method('resource')
            ->willReturn($readSocket = \stream_socket_client('unix:///tmp/read.sock'));
        $select = Select::waitForever()->forRead($read);
        \fwrite($readSocket, 'foo');

        $ready = $select()->match(
            static fn($ready) => $ready,
            static fn() => null,
        );

        $this->assertInstanceOf(Ready::class, $ready);
        $this->assertCount(1, $ready->toRead());
        $this->assertSame($read, $ready->toRead()->find(static fn() => true)->match(
            static fn($stream) => $stream,
            static fn() => null,
        ));
    }
}
