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
    echo $file->readLine()->toString();
}

$file->close();
```

Socket handling:

```php
use Innmind\Stream\{
    Stream\Bidirectional,
    Watch\Select,
};
use Innmind\TimeContinuum\Earth\ElapsedPeriod;

$socket = new Bidirectional(stream_socket_client('unix:///path/to/socket.sock'));
$select = (new Select(new ElapsedPeriod(60 * 1000))) //select with a 1 minute timeout
    ->forRead($socket);

do {
    $ready = $select();

    if ($ready->toRead()->contains($socket)) {
        $socket->write(
            $socket->read()->toUpper()
        );
    }
} while (true);
```

This example will listen for messages sent from the socket `unix:///path/to/socket.sock` and will send it back in upper case.
