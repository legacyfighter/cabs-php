<?php

namespace LegacyFighter\Cabs\Contracts\Legacy;

abstract class OOParadigm
{
    //2. enkapsulacja - ukrycie impl
    private object $field;

    //1. abstrakcja - agent odbierający sygnały
    public function method(): void
    {
        //do sth
    }

    //3. polimorfizm - zmienne zachowania
    protected abstract function abstractStep();
}

//4. dziedziczenie - technika wspierająca polimorizm
class ConcreteType extends OOParadigm
{
    protected function abstractStep()
    {

    }

}
