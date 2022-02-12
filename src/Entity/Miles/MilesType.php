<?php

namespace LegacyFighter\Cabs\Entity\Miles;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\JsonType;

class MilesType extends JsonType
{
    private const EXPIRING = 'expiring';
    private const TWO_STEP = 'two-step';

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if($value instanceof ConstantUntil) {
            return parent::convertToDatabaseValue([
                'type' => self::EXPIRING,
                'amount' => $value->getAmountFor(new \DateTimeImmutable('0000-01-01')),
                'whenExpires' => $value->expiresAt()?->format('Y-m-d H:i:s')
            ], $platform);
        } else if ($value instanceof TwoStepExpiringMiles) {
            return parent::convertToDatabaseValue([
                'type' => self::TWO_STEP,
                'amount' => $value->getAmountFor(new \DateTimeImmutable('0000-01-01')),
                'whenExpires' => $value->expiresAt()?->format('Y-m-d H:i:s'),
                'whenFirstHalfExpires' => $value->getWhenFirstHalfExpires()->format('Y-m-d H:i:s')
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

        if($array['type'] === self::EXPIRING) {
            if (($array['whenExpires'] ?? null) === null) {
                return ConstantUntil::untilForever($array['amount']);
            }

            return ConstantUntil::until($array['amount'], new \DateTimeImmutable($array['whenExpires']));
        } else if($array['type'] === self::TWO_STEP) {
            return new TwoStepExpiringMiles(
                $array['amount'],
                new \DateTimeImmutable($array['whenExpires']),
                new \DateTimeImmutable($array['whenFirstHalfExpires'])
            );
        }

        throw ConversionException::conversionFailed($value, Miles::class);
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }
}
