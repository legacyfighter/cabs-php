<?php

namespace LegacyFighter\Cabs\Geolocation;

final class Distance
{
    private const MILES_TO_KILOMETERS_RATIO = 1.609344;

    private function __construct(private float $km)
    {
    }

    public static function ofKm(float $km): self
    {
        return new self($km);
    }

    public static function zero(): self
    {
        return new self(0);
    }

    public function add(self $travelled): self
    {
        return new self($this->km + $travelled->km);
    }

    public function toKmInFloat(): float
    {
        return $this->km;
    }

    public function printIn(string $unit): string
    {
        if($unit === 'km') {
            if($this->km === ceil($this->km)) {
                return sprintf('%d', round($this->km)).'km';
            }
            return sprintf('%.3f', $this->km).'km';
        }
        if($unit === 'miles') {
            $distance = $this->km / self::MILES_TO_KILOMETERS_RATIO;
            if($distance === ceil($distance)) {
                return sprintf('%d', round($distance)).'miles';
            }
            return sprintf('%.3f', $distance).'miles';
        }
        if($unit === 'm') {
            return sprintf('%d', round($this->km * 1000)).'m';
        }
        throw new \InvalidArgumentException('Invalid unit '.$unit);
    }
}
