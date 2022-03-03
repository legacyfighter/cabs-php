<?php

namespace LegacyFighter\Cabs\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embeddable;

#[Embeddable]
class TimeSlot
{
    private const FIVE_MINUTES = 300;

    #[Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $beginning;

    #[Column(type: 'datetime_immutable', name: '"end"')]
    private \DateTimeImmutable $end;

    private function __construct(\DateTimeImmutable $beginning, \DateTimeImmutable $end)
    {
        if($beginning >= $end) {
            throw new \InvalidArgumentException(sprintf('From %s is after to %s', $beginning->format('Y-m-d H:i:s'),$end->format('Y-m-d H:i:s')));
        }
        $this->beginning = $beginning;
        $this->end = $end;
    }

    public static function of(\DateTimeImmutable $beginning, \DateTimeImmutable $end): self
    {
        return new self($beginning, $end);
    }

    public static function slotThatContains(\DateTimeImmutable $seed): self
    {
        $startOfDay = $seed->setTime(0, 0);
        $secondsFromStartOfDay = $seed->getTimestamp() - $startOfDay->getTimestamp();
        $intervals = floor($secondsFromStartOfDay / self::FIVE_MINUTES);
        $from = $startOfDay->modify(sprintf('+%s seconds', $intervals * self::FIVE_MINUTES));
        return new self($from, $from->modify(sprintf('+%s seconds', self::FIVE_MINUTES)));
    }

    public function contains(\DateTimeImmutable $timestamp): bool
    {
        return $timestamp >= $this->beginning && $timestamp < $this->end;
    }

    public function endsAt(\DateTimeImmutable $timestamp): bool
    {
        return $this->end->getTimestamp() === $timestamp->getTimestamp();
    }

    public function isBefore(\DateTimeImmutable $timestamp): bool
    {
        return $timestamp > $this->end;
    }

    public function prev(): self
    {
        return new self(
            $this->beginning->modify(sprintf('-%s seconds', self::FIVE_MINUTES)),
            $this->end->modify(sprintf('-%s seconds', self::FIVE_MINUTES))
        );
    }

    public function beginning(): \DateTimeImmutable
    {
        return $this->beginning;
    }

    public function end(): \DateTimeImmutable
    {
        return $this->end;
    }
}
