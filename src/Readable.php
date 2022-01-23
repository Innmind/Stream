<?php
declare(strict_types = 1);

namespace Innmind\Stream;

use Innmind\Immutable\{
    Str,
    Maybe,
};

interface Readable extends Stream
{
    /**
     * @param int $length When omitted will read the remaining of the stream
     *
     * @return Maybe<Str>
     */
    public function read(int $length = null): Maybe;

    /**
     * @return Maybe<Str>
     */
    public function readLine(): Maybe;

    /**
     * @return Maybe<string>
     */
    public function toString(): Maybe;
}
