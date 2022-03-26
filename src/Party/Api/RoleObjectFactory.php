<?php

namespace LegacyFighter\Cabs\Party\Api;

use LegacyFighter\Cabs\Party\Model\Party\Party;
use LegacyFighter\Cabs\Party\Model\Party\PartyRelationship;
use LegacyFighter\Cabs\Party\Model\Role\PartyBasedRole;
use Munus\Control\Option;

/**
 * Sample impl based on Class-Instance map.
 * More advanced impls can be case on a DI container: getRole can obtain role instance from the container.
 */
class RoleObjectFactory
{
    /**
     * @var array<string, PartyBasedRole>
     */
    private array $roles = [];

    public static function from(PartyRelationship $relationship): self
    {
        $roleObject = new self();
        $roleObject->addRelationship($relationship);

        return $roleObject;
    }

    public function hasRole(string $role): bool
    {
        return isset($this->roles[$role]);
    }

    public function getRole(string $class): Option
    {
        return Option::of($this->roles[$class] ?? null);
    }

    private function addRelationship(PartyRelationship $relationship): void
    {
        $this->add($relationship->getRoleA(), $relationship->getPartyA());
        $this->add($relationship->getRoleB(), $relationship->getPartyB());
    }

    private function add(string $role, Party $party): void
    {
        //in sake of simplicity: a role name is same as a class name with no mapping between them
        if(!class_exists($role) || !is_subclass_of($role, PartyBasedRole::class)) {
            throw new \InvalidArgumentException();
        }
        $parentClass = get_parent_class($role);
        $key = $parentClass !== PartyBasedRole::class ? $parentClass : $role;

        $this->roles[$key] = new $role($party);
    }
}
