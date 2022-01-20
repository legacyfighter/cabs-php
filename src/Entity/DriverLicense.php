<?php

namespace LegacyFighter\Cabs\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embeddable;

#[Embeddable]
class DriverLicense
{
    public const DRIVER_LICENSE_REGEX = '/^[A-Z9]{5}\d{6}[A-Z9]{2}\d[A-Z]{2}$/';

    #[Column]
    private string $driverLicense;

    private function __construct(string $driverLicense)
    {
        $this->driverLicense = $driverLicense;
    }

    public static function withLicense(string $driverLicense): self
    {
        if($driverLicense === '' || preg_match(self::DRIVER_LICENSE_REGEX, $driverLicense) !== 1) {
            throw new \InvalidArgumentException('Illegal license no = '.$driverLicense);
        }

        return new self($driverLicense);
    }

    public static function withoutValidation(string $driverLicense): self
    {
        return new self($driverLicense);
    }

    public function asString(): string
    {
        return $this->driverLicense;
    }
}
