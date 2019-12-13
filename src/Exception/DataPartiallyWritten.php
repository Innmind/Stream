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
        if ($written > $data->length()) {
            $suggestion = ', it seems you are not using the correct string encoding';
        }

        parent::__construct(\sprintf(
            '%s out of %s written%s',
            $written,
            $data->length(),
            $suggestion ?? '',
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
