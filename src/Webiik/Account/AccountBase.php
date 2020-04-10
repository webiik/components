<?php
declare(strict_types=1);

namespace Webiik\Account;

abstract class AccountBase implements AccountInterface
{
    /**
     * Common authentication status codes and messages
     */
    public const FAILURE = 0,
        METHOD_IS_NOT_IMPLEMENTED = 1,
        INVALID_CREDENTIAL = 2,
        INVALID_PASSWORD = 3,
        INVALID_TOKEN = 4,
        INVALID_KEY = 5,
        ACCOUNT_DOES_NOT_EXIST = 6,
        ACCOUNT_ALREADY_EXISTS = 7,
        ACCOUNT_IS_NOT_ACTIVATED = 8,
        ACCOUNT_IS_BANNED = 9,
        ACCOUNT_IS_DISABLED = 10,
        ACCOUNT_IS_OK = 20;

    public const MSG_FAILURE = 'Failure.',
        MSG_METHOD_IS_NOT_IMPLEMENTED = 'Method is not implemented.',
        MSG_INVALID_CREDENTIAL = 'Invalid credential(s).',
        MSG_INVALID_PASSWORD = 'Invalid password.',
        MSG_INVALID_TOKEN = 'Invalid token.',
        MSG_ACCOUNT_DOES_NOT_EXIST = 'Account does not exist.',
        MSG_ACCOUNT_ALREADY_EXISTS = 'Account already exists.',
        MSG_ACCOUNT_IS_NOT_ACTIVATED = 'Account requires activation.',
        MSG_ACCOUNT_IS_BANNED = 'Account is banned.',
        MSG_ACCOUNT_IS_DISABLED = 'Account is disabled.',
        MSG_ACCOUNT_IS_OK = 'Account is ok';

    /**
     * Account namespace
     * @var string
     */
    public $namespace = '';

    /**
     * @param array $credentials
     * @return User
     * @throws AccountException
     */
    public function auth(array $credentials): User
    {
        throw new AccountException('Method auth() is not implemented.', self::METHOD_IS_NOT_IMPLEMENTED);
    }

    /**
     * @param string $uid
     * @return User
     * @throws AccountException
     */
    public function reAuth(string $uid): User
    {
        throw new AccountException('Method reAuth() is not implemented.', self::METHOD_IS_NOT_IMPLEMENTED);
    }

    /**
     * @param array $credentials
     * @return User
     * @throws AccountException
     */
    public function signup(array $credentials): User
    {
        throw new AccountException('Method signup() is not implemented.', self::METHOD_IS_NOT_IMPLEMENTED);
    }

    /**
     * @param int $uid
     * @param array $data
     * @return User
     * @throws AccountException
     */
    public function update(int $uid, array $data): User
    {
        throw new AccountException('Method update() is not implemented.', self::METHOD_IS_NOT_IMPLEMENTED);
    }

    /**
     * @param int $uid
     * @param int $reason
     * @param array $data
     * @return User
     * @throws AccountException
     */
    public function disable(int $uid, int $reason, array $data = []): User
    {
        throw new AccountException('Method disable() is not implemented.', self::METHOD_IS_NOT_IMPLEMENTED);
    }

    /**
     * @param int $uid
     * @param array $data
     * @return User
     * @throws AccountException
     */
    public function delete(int $uid, array $data = []): User
    {
        throw new AccountException('Method delete() is not implemented.', self::METHOD_IS_NOT_IMPLEMENTED);
    }

    /**
     * @param int $uid
     * @return string
     * @throws AccountException
     */
    public function createToken(): string
    {
        throw new AccountException('Method createToken() is not implemented.', self::METHOD_IS_NOT_IMPLEMENTED);
    }

    /**
     * @param string $token
     * @return User
     * @throws AccountException
     */
    public function activate(string $token): User
    {
        throw new AccountException('Method activate() is not implemented.', self::METHOD_IS_NOT_IMPLEMENTED);
    }

    /**
     * @param string $token
     * @param string $password
     * @return User
     * @throws AccountException
     */
    public function resetPassword(string $token, string $password): User
    {
        throw new AccountException('Method resetPassword() is not implemented.', self::METHOD_IS_NOT_IMPLEMENTED);
    }

    /**
     * @param string $password
     * @return string
     */
    protected function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    /**
     * @param string $password
     * @param string $hash
     * @return bool
     */
    protected function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
}