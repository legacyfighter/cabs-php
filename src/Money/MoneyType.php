<?php

namespace LegacyFighter\Cabs\Money;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\IntegerType;

class MoneyType extends IntegerType
{
    public function getName()
    {
        return 'money';
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return $value === null ? null : Money::from((int) $value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if($value === null) {
            return null;
        }
        assert($value instanceof Money);
        return $value->toInt();
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }
}
