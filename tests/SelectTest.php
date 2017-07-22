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
use Innmind\Server\Control\{
    ServerFactory,
    Server\Command,
    Server\Signal
};
use PHPUnit\Framework\TestCase;

class SelectTest extends TestCase
{
    private $server;
    private $read;
    private $write;
    private $oob;

    public function setUp()
    {
        $this->server = (new ServerFactory)->make();
        $this->read = $this->server->processes()->execute((new Command('php'))->withArgument('fixtures/read.php'));
        $this->write = $this->server->processes()->execute((new Command('php'))->withArgument('fixtures/write.php'));
        $this->oob = $this->server->processes()->execute((new Command('php'))->withArgument('fixtures/oob.php'));
        sleep(1);
    }

    public function tearDown()
    {
        try {
            $this->server->processes()->kill($this->read->pid(), Signal::kill());
            $this->server->processes()->kill($this->write->pid(), Signal::kill());
            $this->server->processes()->kill($this->oob->pid(), Signal::kill());
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
            ->expects($this->once())
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
            ->expects($this->once())
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
            ->expects($this->once())
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
            ->expects($this->once())
            ->method('resource')
            ->willReturn($readSocket = stream_socket_client('unix:///tmp/read.sock'));
        $write = $this->createMock(Selectable::class);
        $write
            ->expects($this->once())
            ->method('resource')
            ->willReturn($writeSocket = stream_socket_client('unix:///tmp/write.sock'));
        $outOfBand = $this->createMock(Selectable::class);
        $outOfBand
            ->expects($this->once())
            ->method('resource')
            ->willReturn($oobSocket = stream_socket_client('tcp://127.0.0.1:1234'));
        $select = (new Select(new ElapsedPeriod(0)))
            ->forRead($read)
            ->forWrite($write)
            ->forOutOfBand($outOfBand);
        fwrite($readSocket, 'foo');
        fwrite($writeSocket, 'foo');
        stream_socket_sendto($oobSocket, 'foo', STREAM_OOB);

        $streams = $select();

        $this->assertInstanceOf(MapInterface::class, $streams);
        $this->assertSame('string', (string) $streams->keyType());
        $this->assertSame(SetInterface::class, (string) $streams->valueType());
        $this->assertCount(3, $streams);
        $this->assertCount(1, $streams->get('read'));
        $this->assertCount(1, $streams->get('write'));
        $this->assertCount(1, $streams->get('out_of_band'));
        $this->assertSame(Selectable::class, (string) $streams->get('read')->type());
        $this->assertSame(Selectable::class, (string) $streams->get('write')->type());
        $this->assertSame(Selectable::class, (string) $streams->get('out_of_band')->type());
        $this->assertSame($read, $streams->get('read')->current());
        $this->assertSame($write, $streams->get('write')->current());
        $this->assertSame($outOfBand, $streams->get('out_of_band')->current());

        $streams = $select();

        $this->assertCount(1, $streams->get('read'));
        $this->assertCount(1, $streams->get('write'));
        $this->assertCount(1, $streams->get('out_of_band'));
    }

    public function testUnwatch()
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

        $select();
        $select2 = $select
            ->unwatch($read)
            ->unwatch($write)
            ->unwatch($outOfBand);

        $this->assertInstanceOf(Select::class, $select);
        $this->assertNotSame($select2, $select);

        $streams = $select2();

        $this->assertCount(0, $streams->get('read'));
        $this->assertCount(0, $streams->get('write'));
        $this->assertCount(0, $streams->get('out_of_band'));
    }
}
