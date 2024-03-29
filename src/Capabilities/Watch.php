<?php
declare(strict_types = 1);

namespace Innmind\Stream\Capabilities;

use Innmind\Stream\Watch as WatchInterface;
use Innmind\TimeContinuum\ElapsedPeriod;

interface Watch
{
    public function timeoutAfter(ElapsedPeriod $timeout): WatchInterface;
    public function waitForever(): WatchInterface;
}
