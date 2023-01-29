# Changelog

## 4.0.0 - 2023-01-29

### Added

- `Innmind\Stream\Capabilities` (implemented by already existing `Innmind\Stream\Streams`)
- `Innmind\Stream\Streams::fromAmbientAuthority()`
- `Innmind\Stream\Stream::resource(): resource`

### Deprecated

- `Innmind\Stream\Streams::of()` use `::fromAmbientAuthority()` instead

### Removed

- `Innmind\Stream\Selectable`

## 3.3.0 - 2022-12-04

### Added

- `Innmind\Stream\Streams` to have a central point from where to open watch for streams

## 3.2.0 - 2022-03-19

### Added

- `Innmind\Stream\Watch\Select::waitForever()` named constructor

## 3.1.0 - 2022-02-22

### Changed

- `Innmind\Stream\Stream::end()` is declared as mutation free

## 3.0.0 - 2022-01-29

### Changed

- `Innmind\Stream\Writable\Stream` constructor is now private, you must use `Stream::of()` instead
- `Innmind\Stream\Writable::write()` now returns `Innmind\Immutable\Either<Innmind\Stream\FailedToWriteToStream|Innmind\Stream\DataPartiallyWritten, Innmind\Stream\Writable>` instead of throwing exceptions
- `Innmind\Stream\Watch\Logger` constructor is now private, you must use `Logger::psr()` instead
- `Innmind\Stream\Watch\Select` constructor is now private, you must use `Select::timeoutAfter()` instead
- `Innmind\Stream\Wtach::__invoke()` now returns `Innmind\Immutable\Maybe<Innmind\Stream\Watch\Ready>` instead of throwing an exception
- `Innmind\Stream\Stream\Stream` constructor is now private, you must use `Stream::of()` instead
- `Innmind\Stream\Stream\Position\Mode` is now an enum
- `Innmind\Stream\Stream\Size` public constants have been moved to the enum `Innmind\Stream\Stream\Size\Unit`
- `Innmind\Stream\Stream\Bidirectional` constructor is now private, you must use `Bidirectional::of()` instead
- `Innmind\Stream\Readable\NonBlocking` constructor is now private, you must use `NonBlocking::of()` instead
- `Innmind\Stream\Readable\Stream` constructor is now private, you must use `Stream::of()` instead
- `Innmind\Stream\Stream::close()` now returns `Innmind\Immutable\Either<Innmind\Stream\FailedToCloseStream, Innmind\Immutable\SideEffect>`
- `Innmind\Stream\Stream::seek()` now returns `Innmind\Immutable\Either<Innmind\Stream\PositionNotSeekable, Innmind\Stream\Stream>`
- `Innmind\Stream\Stream::rewind()` now returns `Innmind\Immutable\Either<Innmind\Stream\PositionNotSeekable, Innmind\Stream\Stream>`
- `Innmind\Stream\Stream::size()` now returns `Innmind\Immutable\Maybe<Innmind\Stream\Stream\Size>`
- `Innmind\Stream\Stream::size()` is now computed on call instead of stream initialization
- `Innmind\Stream\Readable::read()` now returns `Innmind\Immutable\Maybe<Innmind\Immutable\Str>`
- `Innmind\Stream\Readable::readLine()` now returns `Innmind\Immutable\Maybe<Innmind\Immutable\Str>`
- `Innmind\Stream\Readable::toString()` now returns `Innmind\Immutable\Maybe<string>`
- `Innmind\Stream\Exception\DataPartiallyWritten` moved to `Innmind\Stream\DataPartiallyWritten` and is no longer an exception
- `Innmind\Stream\Exception\FailedToCloseStream` moved to `Innmind\Stream\FailedToCloseStream` and is no longer an exception
- `Innmind\Stream\Exception\FailedToWriteToStream` moved to `Innmind\Stream\FailedToWriteToStream` and is no longer an exception
- `Innmind\Stream\Exception\PositionNotSeekable` moved to `Innmind\Stream\PositionNotSeekable` and is no longer an exception

### Removed

- `Innmind\Stream\Stream::knowsSize()`
- `Innmind\Stream\Exception\SelectFailed`
- Support for _out of band_ streams
- Support for php `7.4` and `8.0`
