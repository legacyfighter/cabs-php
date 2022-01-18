<?php

namespace LegacyFighter\Cabs\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;

#[Entity]
class DriverAttribute
{
    public const NAME_PENALTY_POINTS = 'penalty-points';
    public const NAME_NATIONALITY = 'nationality';
    public const NAME_YEARS_OF_EXPERIENCE = 'years-of-experience';
    public const NAME_MEDICAL_EXAMINATION_EXPIRATION_DATE = 'medical-examination-expiration-date';
    public const NAME_MEDICAL_EXAMINATION_REMARKS = 'medical-examination-remarks';
    public const NAME_EMAIL = 'email';
    public const NAME_BIRTHPLACE = 'birthplace';
    public const NAME_COMPANY_NAME = 'company-name';

    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue]
    private int $id;

    #[Column]
    private string $name;

    #[Column]
    private string $value;

    #[ManyToOne(targetEntity: Driver::class)]
    #[JoinColumn(name: 'driver_id')]
    private Driver $driver;

    public function __construct(string $name, string $value, Driver $driver)
    {
        $this->assertName($name);
        $this->name = $name;
        $this->value = $value;
        $this->driver = $driver;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->assertName($name);
        $this->name = $name;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    public function getDriver(): Driver
    {
        return $this->driver;
    }

    public function setDriver(Driver $driver): void
    {
        $this->driver = $driver;
    }

    private function assertName(string $name): void
    {
        if(!in_array($name, [
            self::NAME_BIRTHPLACE,
            self::NAME_COMPANY_NAME,
            self::NAME_EMAIL,
            self::NAME_MEDICAL_EXAMINATION_EXPIRATION_DATE,
            self::NAME_MEDICAL_EXAMINATION_REMARKS,
            self::NAME_PENALTY_POINTS,
            self::NAME_YEARS_OF_EXPERIENCE,
            self::NAME_NATIONALITY
        ], true)) {
            throw new \InvalidArgumentException('Invalid driver attribute name');
        }
    }
}
