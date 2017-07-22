<?php
declare(strict_types = 1);

namespace Innmind\Stream\Exception;

use Innmind\Immutable\Str;

final class DataPartiallyWritten extends RuntimeException
{
    private $data;
    private $written;

    public function __construct(Str $data, int $written)
    {
        parent::__construct(sprintf(
            '%s out of %s written',
            $written,
            $data->length()
        ));
        $this->data = $data;
        $this->written = $written;
    }

    public function data(): Str
    {
        return $this->data;
    }

    public function written(): int
    {
        return $this->written;
    }
}
