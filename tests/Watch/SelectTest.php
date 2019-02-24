<?php
declare(strict_types = 1);

namespace Tests\Innmind\Stream\Watch;

use Innmind\Stream\{
    Watch\Select,
    Watch\Ready,
    Watch,
    Selectable
};
use Innmind\TimeContinuum\ElapsedPeriod;
use Innmind\Immutable\{
    MapInterface,
    SetInterface
};
use Symfony\Component\Process\Process;
use PHPUnit\Framework\TestCase;

class SelectTest extends TestCase
{
    private $read;
    private $write;
    private $oob;

    public function setUp(): void
    {
        $this->read = new Process(['php', 'fixtures/read.php']);
        $this->write = new Process(['php', 'fixtures/write.php']);
        $this->oob = new Process(['php', 'fixtures/oob.php']);
        $this->read = $this->read->start();
        $this->write = $this->write->start();
        $this->oob = $this->oob->start();
        sleep(1);
    }

    public function tearDown(): void
    {
        try {
            $this->read->stop(10, SIGKILL);
            $this->write->stop(10, SIGKILL);
            $this->oob->stop(10, SIGKILL);
        } catch (\Throwable $e) {
            //pass
        }
    }

    public function testInterface()
    {
        $this->assertInstanceOf(
            Watch::class,
            new Select(new ElapsedPeriod(0))
        );
    }

    public function testForRead()
    {
        $select = new Select(new ElapsedPeriod(0));
        $resource = fopen('php://temp', 'w');
        $stream = $this->createMock(Selectable::class);
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
        $select = new Select(new ElapsedPeriod(0));
        $resource = fopen('php://temp', 'w');
        $stream = $this->createMock(Selectable::class);
        $stream
            ->expects($this->exactly(2))
            ->method('resource')
            ->willReturn($resource);

        $select2 = $select->forWrite($stream);

        $this->assertInstanceOf(Select::class, $select2);
        $this->assertNotSame($select2, $select);
    }

    public function testForOutOfBand()
    {
        $select = new Select(new ElapsedPeriod(0));
        $resource = fopen('php://temp', 'w');
        $stream = $this->createMock(Selectable::class);
        $stream
            ->expects($this->exactly(2))
            ->method('resource')
            ->willReturn($resource);

        $select2 = $select->forOutOfBand($stream);

        $this->assertInstanceOf(Select::class, $select2);
        $this->assertNotSame($select2, $select);
    }

    public function testInvokeWhenNoStream()
    {
        $ready = (new Select(new ElapsedPeriod(0)))();

        $this->assertInstanceOf(Ready::class, $ready);
        $this->assertCount(0, $ready->toRead());
        $this->assertCount(0, $ready->toWrite());
        $this->assertCount(0, $ready->toOutOfBand());
    }

    public function testInvoke()
    {
        $read = $this->createMock(Selectable::class);
        $read
            ->expects($this->exactly(2))
            ->method('resource')
            ->willReturn($readSocket = stream_socket_client('unix:///tmp/read.sock'));
        $write = $this->createMock(Selectable::class);
        $write
            ->expects($this->exactly(2))
            ->method('resource')
            ->willReturn($writeSocket = stream_socket_client('unix:///tmp/write.sock'));
        $outOfBand = $this->createMock(Selectable::class);
        $outOfBand
            ->expects($this->exactly(2))
            ->method('resource')
            ->willReturn($oobSocket = stream_socket_client('tcp://127.0.0.1:1234'));
        $select = (new Select(new ElapsedPeriod(0)))
            ->forRead($read)
            ->forWrite($write)
            ->forOutOfBand($outOfBand);
        fwrite($readSocket, 'foo');
        fwrite($writeSocket, 'foo');
        stream_socket_sendto($oobSocket, 'foo', STREAM_OOB);

        $ready = $select();

        $this->assertInstanceOf(Ready::class, $ready);
        $this->assertCount(1, $ready->toRead());
        $this->assertCount(1, $ready->toWrite());
        $this->assertCount(1, $ready->toOutOfBand());
        $this->assertSame($read, $ready->toRead()->current());
        $this->assertSame($write, $ready->toWrite()->current());
        $this->assertSame($outOfBand, $ready->toOutOfBand()->current());

        $ready = $select();

        $this->assertCount(1, $ready->toRead());
        $this->assertCount(1, $ready->toWrite());
        $this->assertCount(1, $ready->toOutOfBand());
    }

    public function testUnwatch()
    {
        $read = $this->createMock(Selectable::class);
        $read
            ->expects($this->exactly(3))
            ->method('resource')
            ->willReturn($readSocket = stream_socket_client('unix:///tmp/read.sock'));
        $write = $this->createMock(Selectable::class);
        $write
            ->expects($this->exactly(3))
            ->method('resource')
            ->willReturn($writeSocket = stream_socket_client('unix:///tmp/write.sock'));
        $outOfBand = $this->createMock(Selectable::class);
        $outOfBand
            ->expects($this->exactly(3))
            ->method('resource')
            ->willReturn($oobSocket = stream_socket_client('tcp://127.0.0.1:1234'));
        $select = (new Select(new ElapsedPeriod(0)))
            ->forRead($read)
            ->forWrite($write)
            ->forOutOfBand($outOfBand);
        fwrite($readSocket, 'foo');
        fwrite($writeSocket, 'foo');
        stream_socket_sendto($oobSocket, 'foo', STREAM_OOB);

        $select();
        $select2 = $select
            ->unwatch($read)
            ->unwatch($write)
            ->unwatch($outOfBand);

        $this->assertInstanceOf(Select::class, $select);
        $this->assertNotSame($select2, $select);

        $streams = $select2();

        $this->assertCount(0, $streams->toRead());
        $this->assertCount(0, $streams->toWrite());
        $this->assertCount(0, $streams->toOutOfBand());
    }
}
