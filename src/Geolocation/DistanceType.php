<?php

namespace LegacyFighter\Cabs\Geolocation;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\FloatType;

class DistanceType extends FloatType
{
    public function getName()
    {
        return 'distance';
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return $value === null ? null : Distance::ofKm((float) $value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if($value === null) {
            return null;
        }
        assert($value instanceof Distance);
        return $value->toKmInFloat();
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }
}
