<?php
declare(strict_types=1);

namespace Webiik\Account;

interface AccountInterface
{
    /**
     * Authenticate a user with $credentials.
     * On success returns User, on error throw AccountException.
     *
     * Possible exception status codes:
     * METHOD_IS_NOT_IMPLEMENTED, INVALID_CREDENTIAL, INVALID_PASSWORD,
     * ACCOUNT_DOES_NOT_EXIST, ACCOUNT_IS_NOT_ACTIVATED, ACCOUNT_IS_BANNED,
     * ACCOUNT_IS_DISABLED, FAILURE
     *
     * @param array $credentials
     * @throws AccountException
     * @return User
     */
    public function auth(array $credentials): User;

    /**
     * Sign up a user with $credentials.
     * On success return User, on error throw AccountException.
     *
     * Possible exception status codes:
     * METHOD_IS_NOT_IMPLEMENTED, INVALID_CREDENTIAL,
     * ACCOUNT_ALREADY_EXISTS, FAILURE
     *
     * @param array $credentials
     * @throws AccountException
     * @return User
     */
    public function signup(array $credentials): User;

    /**
     * Update $data on account with id $uid.
     * On success return User, on error throw AccountException.
     *
     * Possible exception status codes:
     * METHOD_IS_NOT_IMPLEMENTED, ACCOUNT_DOES_NOT_EXIST,
     * INVALID_KEY, FAILURE
     *
     * @param int $uid
     * @param array $data
     * @throws AccountException
     * @return User
     */
    public function update(int $uid, array $data): User;

    /**
     * Set account status to ACCOUNT_IS_DISABLED or ACCOUNT_IS_BANNED on account with id $uid.
     * On success return User, on error throw AccountException.
     *
     * Possible exception status codes:
     * METHOD_IS_NOT_IMPLEMENTED, ACCOUNT_DOES_NOT_EXIST, FAILURE
     *
     * @param int $uid
     * @param int $reason
     * @param array $data
     * @throws AccountException
     * @return User
     */
    public function disable(int $uid, int $reason, array $data = []): User;

    /**
     * Delete an account with id $uid.
     * On success return User, on error throw AccountException.
     *
     * Possible exception status codes:
     * METHOD_IS_NOT_IMPLEMENTED, ACCOUNT_DOES_NOT_EXIST, FAILURE
     *
     * @param int $uid
     * @param array $data
     * @throws AccountException
     * @return User
     */
    public function delete(int $uid, array $data = []): User;

    /**
     * Create and return time limited security token.
     * On error throws AccountException.
     *
     * Possible exception status codes:
     * METHOD_IS_NOT_IMPLEMENTED, FAILURE
     *
     * @throws AccountException
     * @return string
     */
    public function createToken(): string;

    /**
     * Activates an account by valid $token.
     * On success return User, on error throw AccountException.
     *
     * Possible exception status codes:
     * METHOD_IS_NOT_IMPLEMENTED, INVALID_TOKEN, FAILURE
     *
     * @throws AccountException
     * @param string $token
     * @return User
     */
    public function activate(string $token): User;

    /**
     * Updates account $password by valid $token.
     * On success return User, on error throw AccountException.
     *
     * Possible exception status codes:
     * METHOD_IS_NOT_IMPLEMENTED, INVALID_TOKEN, FAILURE
     *
     * @throws AccountException
     * @param string $token
     * @param string $password
     * @return User
     */
    public function resetPassword(string $token, string $password): User;
}
