<?php
declare(strict_types=1);

namespace Webiik\Account\Accounts;

use Webiik\Account\User;

interface AccountInterface
{
    public function auth(array $credentials): User;

    public function signup(array $credentials): User;

    public function update(array $id, array $data): User;

    public function disable(array $id, int $reason): User;

    public function delete(array $id): User;

    public function createActivation($uid): array;

    public function activate(array $id): User;

    public function createPasswordReset($uid): array;

    public function resetPassword(array $id, string $password): User;
}
