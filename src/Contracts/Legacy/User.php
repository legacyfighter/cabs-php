<?php

namespace LegacyFighter\Cabs\Contracts\Legacy;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use LegacyFighter\Cabs\Common\BaseEntity;

#[Entity]
#[Table(name: 'users')]
class User extends BaseEntity
{

}
