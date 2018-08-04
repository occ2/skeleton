<?php
namespace app\User\models\facades;

use Nette\Security\IAuthorizator;
use Nette\Security\Permission;

/**
 * AuthorizationFacade
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
final class AuthorizationFacade extends Permission implements IAuthorizator
{
    const ROLE_GUEST="guest",
          ROLE_AUTHENTICATED="authenticated",
          ROLE_ADMINISTRATOR="administrator",
          RESOURCE_PROFILE="profile",
          PRIVILEGE_READ="read",
          PRIVILEGE_WRITE="write",
          PRIVILEGE_DELETE="delete"
        ;

    /**
     * @param array $config
     * @return void
     */
    public function __construct(array $config)
    {
        $this->addRole(static::ROLE_GUEST);
        $this->addRole(static::ROLE_AUTHENTICATED);
        $this->addRole(static::ROLE_ADMINISTRATOR, static::ROLE_AUTHENTICATED);
        $this->allow(static::ROLE_ADMINISTRATOR);

        $this->setupResources($config);
        $this->setupRoles($config);
        $this->setupAllow($config);
        $this->setupDeny($config);
        return;
    }

    /**
     * @param array $config
     * @return void
     */
    protected function setupResources($config)
    {
        $this->addResource(static::RESOURCE_PROFILE);
        if (isset($config["resources"]) && !empty($config["resources"])) {
            foreach ($config["resources"] as $resource=>$parent) {
                $this->addResource($resource, $parent);
            }
        }
        return;
    }

    /**
     * @param array $config
     * @return void
     */
    protected function setupRoles($config)
    {
        if (isset($config["roles"]) && !empty($config["roles"])) {
            foreach ($config["roles"] as $role=>$parents) {
                $this->addRole($role, $parents);
            }
        }
        return;
    }

    /**
     * @param array $config
     * @return void
     */
    protected function setupAllow($config)
    {
        $this->allow(static::ROLE_AUTHENTICATED, static::RESOURCE_PROFILE, IAuthorizator::ALL);
        if (isset($config["allow"]) && !empty($config["allow"])) {
            foreach ($config["allow"] as $allow) {
                $this->allow(
                    isset($allow["role"]) ? $allow["role"] : static::ALL,
                             isset($allow["resource"]) ? $allow["resource"] : static::ALL,
                             isset($allow["privilege"]) ? $allow["privilege"] : static::ALL,
                             (isset($allow["assertion"]) && is_callable($allow["assertion"])) ? $allow["assertion"] : null
                );
            }
        }
        return;
    }

    /**
     * @param array $config
     * @return void
     */
    protected function setupDeny($config)
    {
        if (isset($config["deny"]) && !empty($config["deny"])) {
            foreach ($config["deny"] as $allow) {
                $this->allow(
                    isset($allow["role"]) ? $allow["role"] : static::ALL,
                             isset($allow["resource"]) ? $allow["resource"] : static::ALL,
                             isset($allow["privilege"]) ? $allow["privilege"] : static::ALL,
                             (isset($allow["assertion"]) && is_callable($allow["assertion"])) ? $allow["assertion"] : null
                );
            }
        }
        return;
    }
}