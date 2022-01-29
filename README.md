# Stream

[![Build Status](https://github.com/Innmind/Stream/workflows/CI/badge.svg?branch=master)](https://github.com/Innmind/Stream/actions?query=workflow%3ACI)
[![codecov](https://codecov.io/gh/Innmind/Stream/branch/develop/graph/badge.svg)](https://codecov.io/gh/Innmind/Stream)
[![Type Coverage](https://shepherd.dev/github/Innmind/Stream/coverage.svg)](https://shepherd.dev/github/Innmind/Stream)

Simple wrapper to work with resource streams.

## Installation

```sh
composer require innmind/stream
```

## Usage

File handling:

```php
use Innmind\Stream\Readable\Stream;
use Innmind\Url\Path;

$file = Stream::open(Path::of('/some/path/to/a/file'));

while (!$file->end()) {
    echo $file->readLine()->match(
        static fn($line) => $line->toString(),
        static fn() => throw new \Exception('failed to read the stream'),
    );
}

$file->close()->match(
    static fn() => null, // closed correctly
    static fn() => throw new \Exception('failed to close the stream'),
);
```

Socket handling:

```php
use Innmind\Stream\{
    Stream\Bidirectional,
    Watch\Select,
};
use Innmind\TimeContinuum\Earth\ElapsedPeriod;
use Innmind\Immutable\Either;

$socket = new Bidirectional(stream_socket_client('unix:///path/to/socket.sock'));
$select = Select::timeoutAfter(new ElapsedPeriod(60 * 1000)) // select with a 1 minute timeout
    ->forRead($socket);

do {
    $socket = $select()
        ->filter(static fn($ready) => $ready->toRead()->contains($socket))
        ->flatMap(static fn() => $socket->read())
        ->map(static fn($data) => $data->toUpper())
        ->match(
            static fn($data) => $socket->write($data),
            static fn() => Either::right($socket), // no data to send
        )
        ->match(
            static fn($socket) => $socket, // data sent back
            static fn($error) => throw new \Exception(\get_class($error)),
        );
} while (true);
```

This example will listen for messages sent from the socket `unix:///path/to/socket.sock` and will send it back in upper case.
