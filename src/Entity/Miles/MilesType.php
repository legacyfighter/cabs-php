<?php

namespace LegacyFighter\Cabs\Entity\Miles;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\JsonType;

class MilesType extends JsonType
{
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if($value instanceof ConstantUntil) {
            return parent::convertToDatabaseValue([
                'type' => 'expiring',
                'amount' => $value->getAmountFor(new \DateTimeImmutable('0000-01-01')),
                'whenExpires' => $value->expiresAt()?->format('Y-m-d H:i:s')
            ], $platform);
        }

        throw ConversionException::conversionFailedInvalidType($value, 'miles', [ConstantUntil::class]);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        $array = parent::convertToPHPValue($value, $platform);
        if(!isset($array['type'])) {
            throw ConversionException::conversionFailed($value, Miles::class);
        }

        if($array['type'] === 'expiring') {
            if (($array['whenExpires'] ?? null) === null) {
                return ConstantUntil::untilForever($array['amount']);
            }

            return ConstantUntil::until($array['amount'], new \DateTimeImmutable($array['whenExpires']));
        }

        throw ConversionException::conversionFailed($value, Miles::class);
    }

}
