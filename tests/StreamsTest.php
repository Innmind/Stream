<?php
declare(strict_types = 1);

namespace Tests\Innmind\Stream;

use Innmind\Stream\{
    Streams,
    Capabilities,
    Watch\Select,
};
use Innmind\TimeContinuum\Earth\ElapsedPeriod;
use Innmind\Url\Path;
use Innmind\Immutable\Str;
use PHPUnit\Framework\TestCase;

class StreamsTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            Capabilities::class,
            Streams::fromAmbientAuthority(),
        );
    }

    public function testOpeningATemporaryStreamAlwaysReturnANewOne()
    {
        $streams = Streams::fromAmbientAuthority();
        $a = $streams->temporary()->new()->write(Str::of('a'))->match(
            static fn($a) => $a,
            static fn() => null,
        );
        $b = $streams->temporary()->new()->write(Str::of('b'))->match(
            static fn($a) => $a,
            static fn() => null,
        );

        $this->assertNotNull($a);
        $this->assertNotNull($b);
        $this->assertNotSame($a, $b);
        $this->assertSame(
            'a',
            $a->toString()->match(
                static fn($string) => $string,
                static fn() => null,
            ),
        );
        $this->assertSame(
            'b',
            $b->toString()->match(
                static fn($string) => $string,
                static fn() => null,
            ),
        );
    }

    public function testOpenReadable()
    {
        $self = Streams::fromAmbientAuthority()
            ->readable()
            ->open(Path::of(__FILE__))
            ->toString()
            ->match(
                static fn($self) => $self,
                static fn() => null,
            );

        $this->assertSame(\file_get_contents(__FILE__), $self);
    }

    public function testOpenWritable()
    {
        $path = Path::of(\tempnam(\sys_get_temp_dir(), 'streams'));
        $streams = Streams::fromAmbientAuthority();
        $streams
            ->writable()
            ->open($path)
            ->write(Str::of('foo'));

        $this->assertSame(
            'foo',
            $streams
                ->readable()
                ->open($path)
                ->toString()
                ->match(
                    static fn($string) => $string,
                    static fn() => null,
                ),
        );
    }

    public function testWatch()
    {
        $this->assertInstanceOf(
            Select::class,
            Streams::fromAmbientAuthority()
                ->watch()
                ->waitForever(),
        );
        $this->assertInstanceOf(
            Select::class,
            Streams::fromAmbientAuthority()
                ->watch()
                ->timeoutAfter(new ElapsedPeriod(1)),
        );
    }
}
