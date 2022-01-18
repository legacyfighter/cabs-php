<?php

declare(strict_types=1);

namespace LegacyFighter\Cabs\Common;

final class ObjectHash
{
    /**
     * @param string|int ...$properties
     */
    public static function hash(...$properties): int
    {
        return crc32(join('', $properties));
    }
}
