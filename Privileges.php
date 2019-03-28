<?php
declare(strict_types=1);

namespace Webiik\Privileges;

class Privileges
{
    /**
     * All allowed user roles
     * @var array
     */
    private $roles = [];

    /**
     * All allowed resources and resource privileges
     * @var array
     */
    private $resources = [];

    /**
     * All allowed combinations of role, resource and privileges
     * @var array
     */
    private $allowed = [];

    /**
     * Add allowed role
     * @param string $role
     */
    public function addRole(string $role): void
    {
        $this->roles[$role] = true;
    }

    /**
     * Add allowed resource and resource privileges
     * @param string $resource
     * @param array $privileges
     */
    public function addResource(string $resource, array $privileges): void
    {
        if ($privileges[0] != 'all') {
            $this->resources[$resource] = $privileges;
        }
    }

    /**
     * Set allowed combination of role, resource and privileges
     * @param string $role
     * @param string $resource
     * @param array $privileges
     */
    public function allow(string $role, string $resource, array $privileges): void
    {
        if (!isset($this->roles[$role])) {
            return;
        }

        if (!isset($this->resources[$resource])) {
            return;
        }

        if (!$privileges) {
            return;
        }

        if ($privileges[0] == 'all') {
            $this->allowed[$role][$resource] = $this->resources[$resource];
            return;
        }

        foreach ($privileges as $privilege) {
            if (!in_array($privilege, $this->resources[$resource])) {
                return;
            }
        }

        $this->allowed[$role][$resource] = $privileges;
    }

    /**
     * Check if user with $role can do $privilege on $resource
     * @param string $role
     * @param string $resource
     * @param string $privilege
     * @return bool
     */
    public function isAllowed(string $role, string $resource, string $privilege): bool
    {
        if (!isset($this->allowed[$role])) {
            return false;
        }

        if (!isset($this->allowed[$role][$resource])) {
            return false;
        }

        if (!in_array($privilege, $this->allowed[$role][$resource])) {
            return false;
        }

        return true;
    }
}