<?php
declare(strict_types=1);

namespace Webiik\Account;

class Account extends AccountBase
{
    /**
     * @var array
     */
    private $accounts = [];

    /**
     * @var
     */
    private $currentAccount;

    /**
     * Add an implementation of account
     * @param string $name
     * @param callable $accountFactory
     */
    public function addAccount(string $name, callable $accountFactory): void
    {
        $this->accounts[$name] = $accountFactory;
    }

    /**
     * Set account to be used when calling custom account related methods
     * @param string $name
     */
    public function useAccount(string $name): void
    {
        $this->currentAccount = $name;
    }

    /**
     * Sets authentication namespace
     * @param string $namespace
     */
    public function setNamespace(string $namespace): void
    {
        $this->namespace = $namespace;
    }

    /**
     * @param array $credentials
     * @return User
     * @throws AccountException
     */
    public function auth(array $credentials): User
    {
        return $this->getAccount()->auth($credentials);
    }

    /**
     * @param string $uid
     * @return User
     * @throws AccountException
     */
    public function reAuth(string $uid): User
    {
        return $this->getAccount()->reAuth($uid);
    }

    /**
     * @param array $credentials
     * @return User
     * @throws AccountException
     */
    public function signup(array $credentials): User
    {
        return $this->getAccount()->signup($credentials);
    }

    /**
     * @param int $uid
     * @param array $data
     * @return User
     * @throws AccountException
     */
    public function update(int $uid, array $data): User
    {
        return $this->getAccount()->update($uid, $data);
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
        return $this->getAccount()->disable($uid, $reason);
    }

    /**
     * @param int $uid
     * @param array $data
     * @return User
     * @throws AccountException
     */
    public function delete(int $uid, array $data = []): User
    {
        return $this->getAccount()->delete($uid);
    }

    /**
     * @return string
     * @throws AccountException
     */
    public function createToken(): string
    {
        return $this->getAccount()->createToken();
    }

    /**
     * @param string $token
     * @return User
     * @throws AccountException
     */
    public function activate(string $token): User
    {
        return $this->getAccount()->activate($token);
    }

    /**
     * @param string $token
     * @param string $password
     * @return User
     * @throws AccountException
     */
    public function resetPassword(string $token, string $password): User
    {
        return $this->getAccount()->resetPassword($token, $password);
    }

    /**
     * @return AccountInterface
     */
    private function getAccount(): AccountInterface
    {
        // Instantiate storage only once
        if (is_callable($this->accounts[$this->currentAccount])) {
            $account = $this->accounts[$this->currentAccount];
            $this->accounts[$this->currentAccount] = $account();
        }

        // Set namespace
        $this->accounts[$this->currentAccount]->namespace = $this->namespace;

        return $this->accounts[$this->currentAccount];
    }
}