# Stream

| `master` | `develop` |
|----------|-----------|
| [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Innmind/Stream/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Innmind/Stream/?branch=master) | [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Innmind/Stream/badges/quality-score.png?b=develop)](https://scrutinizer-ci.com/g/Innmind/Stream/?branch=develop) |
| [![Code Coverage](https://scrutinizer-ci.com/g/Innmind/Stream/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Innmind/Stream/?branch=master) | [![Code Coverage](https://scrutinizer-ci.com/g/Innmind/Stream/badges/coverage.png?b=develop)](https://scrutinizer-ci.com/g/Innmind/Stream/?branch=develop) |
| [![Build Status](https://scrutinizer-ci.com/g/Innmind/Stream/badges/build.png?b=master)](https://scrutinizer-ci.com/g/Innmind/Stream/build-status/master) | [![Build Status](https://scrutinizer-ci.com/g/Innmind/Stream/badges/build.png?b=develop)](https://scrutinizer-ci.com/g/Innmind/Stream/build-status/develop) |

Simple wrapper to work with resource streams.

## Installation

```sh
composer require innmind/stream
```

## Usage

File handling:

```php
use Innmind\Stream\Readable\Stream;

$file = new Stream(fopen('/some/path/to/a/file', 'r'));

while (!$file->end()) {
    echo $file->readLine();
}

$file->close();
```

Socket handling:

```php
use Innmind\Stream\{
    Stream\Bidirectional,
    Watch\Select
};
use Innmind\TimeContinuum\ElapsedPeriod;

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
