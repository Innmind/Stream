<?php
declare(strict_types = 1);

namespace Innmind\Stream;

use Innmind\Immutable\{
    Str,
    Either,
};

interface Writable extends Stream
{
    /**
     * @return Either<FailedToWriteToStream|DataPartiallyWritten, self>
     */
    public function write(Str $data): Either;
}
