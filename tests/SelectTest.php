<?php
declare(strict_types = 1);

namespace Tests\Innmind\Stream;

use Innmind\Stream\{
    Select,
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

    public function setUp(): void
    {
        $this->read = new Process(['php', 'fixtures/read.php']);
        $this->write = new Process(['php', 'fixtures/write.php']);
        $this->read = $this->read->start();
        $this->write = $this->write->start();
        sleep(1);
    }

    public function tearDown(): void
    {
        try {
            $this->read->stop(10, SIGKILL);
            $this->write->stop(10, SIGKILL);
        } catch (\Throwable $e) {
            //pass
        }
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
        $streams = (new Select(new ElapsedPeriod(0)))();

        $this->assertInstanceOf(MapInterface::class, $streams);
        $this->assertSame('string', (string) $streams->keyType());
        $this->assertSame(SetInterface::class, (string) $streams->valueType());
        $this->assertCount(3, $streams);
        $this->assertCount(0, $streams->get('read'));
        $this->assertCount(0, $streams->get('write'));
        $this->assertCount(0, $streams->get('out_of_band'));
        $this->assertSame(Selectable::class, (string) $streams->get('read')->type());
        $this->assertSame(Selectable::class, (string) $streams->get('write')->type());
        $this->assertSame(Selectable::class, (string) $streams->get('out_of_band')->type());
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
        $select = (new Select(new ElapsedPeriod(0)))
            ->forRead($read)
            ->forWrite($write);
        fwrite($readSocket, 'foo');
        fwrite($writeSocket, 'foo');

        $streams = $select();

        $this->assertInstanceOf(MapInterface::class, $streams);
        $this->assertSame('string', (string) $streams->keyType());
        $this->assertSame(SetInterface::class, (string) $streams->valueType());
        $this->assertCount(3, $streams);
        $this->assertCount(1, $streams->get('read'));
        $this->assertCount(1, $streams->get('write'));
        $this->assertSame(Selectable::class, (string) $streams->get('read')->type());
        $this->assertSame(Selectable::class, (string) $streams->get('write')->type());
        $this->assertSame(Selectable::class, (string) $streams->get('out_of_band')->type());
        $this->assertSame($read, $streams->get('read')->current());
        $this->assertSame($write, $streams->get('write')->current());

        $streams = $select();

        $this->assertCount(1, $streams->get('read'));
        $this->assertCount(1, $streams->get('write'));
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
        $select = (new Select(new ElapsedPeriod(0)))
            ->forRead($read)
            ->forWrite($write);
        fwrite($readSocket, 'foo');
        fwrite($writeSocket, 'foo');

        $select();
        $select2 = $select
            ->unwatch($read)
            ->unwatch($write);

        $this->assertInstanceOf(Select::class, $select);
        $this->assertNotSame($select2, $select);

        $streams = $select2();

        $this->assertCount(0, $streams->get('read'));
        $this->assertCount(0, $streams->get('write'));
    }
}
