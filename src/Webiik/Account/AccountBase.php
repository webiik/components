<?php
declare(strict_types=1);

namespace Webiik\Account\Accounts;

use Webiik\Privileges\Privileges;

abstract class AccountBase implements AccountInterface
{
    public const FAILURE = 0,
        METHOD_IS_NOT_IMPLEMENTED = 1,
        INVALID_CREDENTIAL = 2,
        ALREADY_EXISTS = 3,
        REQUIRES_ACTIVATION = 4,
        IS_BANNED = 5,
        IS_DISABLED = 6,
        IS_OK = 7;

    /**
     * @var Privileges
     */
    private $privileges;

     */
    /**
     * AccountBase constructor.
     * @param Privileges $privileges
     */
    public function __construct(Privileges $privileges)
    {
        $this->privileges = $privileges;
    }

    public function auth(array $credentials): User
    {
        if (isset($credentials['email'], $credentials['password'])) {
            list('email' => $email, 'password' => $password, 'id' => $id) = $credentials;
        }

        if (isset($credentials['id'])) {
            list('id' => $id) = $credentials;
        }

        return new User($id, $role, $info, $status, $this->privileges);
    }

    public function signup(array $credentials): User
    {
        // TODO: Implement signup() method.
    }

    public function update(array $id, array $data): User
    {
        // TODO: Implement update() method.
    }

    public function disable(array $id, int $reason): User
    {
        // TODO: Implement disable() method.
    }

    public function delete(array $id): User
    {
        // TODO: Implement delete() method.
    }

    public function createActivation($uid): array
    {
        // TODO: Implement createActivation() method.
    }

    public function activate(array $id): User
    {
        // TODO: Implement activate() method.
    }

    public function createPasswordReset($uid): array
    {
        // TODO: Implement createPasswordReset() method.
    }

    public function resetPassword(array $id, string $password): User
    {
        // TODO: Implement resetPassword() method.
    }

}