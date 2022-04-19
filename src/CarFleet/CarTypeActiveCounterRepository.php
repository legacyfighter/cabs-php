<?php

namespace LegacyFighter\Cabs\CarFleet;

use Doctrine\ORM\EntityManagerInterface;

class CarTypeActiveCounterRepository
{
    public function __construct(private EntityManagerInterface $em) {}

    public function findByCarClass(string $carClass): ?CarTypeActiveCounter
    {
        return $this->em->find(CarTypeActiveCounter::class, $carClass);
    }

    public function save(CarTypeActiveCounter $carTypeActiveCounter): void
    {
        $this->em->persist($carTypeActiveCounter);
    }

    public function incrementCounter(string $carClass): void
    {
        $this->em->getConnection()->executeQuery('
            UPDATE car_type_active_counter counter SET active_cars_counter = active_cars_counter + 1 
            WHERE counter.car_class = :car_class', [
            'car_class' => $carClass
        ]);
        $this->em->clear(CarTypeActiveCounter::class);
    }

    public function decrementCounter(string $carClass): void
    {
        $this->em->getConnection()->executeQuery('
            UPDATE car_type_active_counter counter SET active_cars_counter = active_cars_counter - 1 
            WHERE counter.car_class = :car_class', [
            'car_class' => $carClass
        ]);
        $this->em->clear(CarTypeActiveCounter::class);
    }

    public function delete(CarTypeActiveCounter $carTypeActiveCounter): void
    {
        $this->em->remove($carTypeActiveCounter);
        $this->em->flush();
    }
}
