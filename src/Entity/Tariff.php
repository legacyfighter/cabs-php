<?php

namespace LegacyFighter\Cabs\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embeddable;
use LegacyFighter\Cabs\Distance\Distance;
use LegacyFighter\Cabs\Money\Money;

#[Embeddable]
class Tariff
{
    private const BASE_FEE = 8;

    #[Column(type: 'float')]
    private float $kmRate;

    #[Column]
    private string $name;

    #[Column(type: 'integer')]
    private int $baseFee;

    private function __construct(float $kmRate, string $name, int $baseFee)
    {
        $this->kmRate = $kmRate;
        $this->name = $name;
        $this->baseFee = $baseFee;
    }

    public static function ofTime(\DateTimeImmutable $time): self
    {
        if(($time->format('n') === '12' && $time->format('j') === '31') ||
            ($time->format('n') === '1' && $time->format('j') === '1' && (int) $time->format('G') <= 6)
        ) {
            return new self(3.5, 'Sylwester', self::BASE_FEE + 3);
        } else {
            // piątek i sobota po 17 do 6 następnego dnia
            if(($time->format('l') === 'Friday' && (int) $time->format('G') >= 17) ||
                ($time->format('l') === 'Saturday' && (int) $time->format('G') <= 6) ||
                ($time->format('l') === 'Saturday' && (int) $time->format('G') >= 17) ||
                ($time->format('l') === 'Sunday' && (int) $time->format('G') <= 6)
            ) {
                return new self(2.5, 'Weekend+', self::BASE_FEE + 2);
            } else {
                // pozostałe godziny weekendu
                if(($time->format('l') === 'Saturday' && (int) $time->format('G') > 6 && (int) $time->format('G') < 17) ||
                    ($time->format('l') === 'Sunday' && (int) $time->format('G') > 6)
                ) {
                    return new self(1.5, 'Weekend', self::BASE_FEE);
                } else {
                    // tydzień roboczy
                    return new self(1.0, 'Standard', self::BASE_FEE + 1);
                }
            }
        }
    }

    public function calculateCost(Distance $distance): Money
    {
        return Money::from((int) (round($distance->toKmInFloat() * $this->kmRate + $this->baseFee, 2) * 100));
    }

    public function getKmRate(): float
    {
        return $this->kmRate;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getBaseFee(): int
    {
        return $this->baseFee;
    }
}
