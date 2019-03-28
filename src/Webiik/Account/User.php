<?php
declare(strict_types=1);

namespace Webiik\Account;

class User
{
    /**
     * User account unique id
     * @var mixed
     */
    private $id;

    /**
     * User role
     * @var string
     */
    private $role;

    /**
     * Additional user account info
     * @var array
     */
    private $info;

    /**
     * User account status
     * Usually: AccountBase::ACCOUNT_IS_OK or AccountBase::ACCOUNT_IS_NOT_ACTIVATED
     * @var int
     */
    private $status;

    /**
     * User constructor.
     * @param int $status
     * @param mixed $id
     * @param string $role
     * @param array $info
     * @throws AccountException
     */
    public function __construct(int $status, $id, string $role = '', array $info = [])
    {
        $this->id = $id;
        $this->role = $role;
        $this->info = $info;
        $this->status = $status;
        $this->check();
    }

    /**
     * Get unique user account id
     * @return int|string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get user role
     * @return string
     */
    public function getRole(): string
    {
        return $this->role;
    }

    /**
     * Get user account additional info
     * @return array
     */
    public function getInfo(): array
    {
        return $this->info;
    }

    /**
     * Check if user has given role
     * @param string $role
     * @return bool
     */
    public function hasRole(string $role): bool
    {
        return $this->role == $role;
    }

    /**
     * Check if user account requires activation
     * @return bool
     */
    public function needsActivation(): bool
    {
        return $this->status == AccountBase::ACCOUNT_IS_NOT_ACTIVATED;
    }

    /**
     * @throws AccountException
     */
    private function check()
    {
        if(!$this->id) {
            throw new AccountException('Class: User, id can\'t be empty', AccountBase::FAILURE );
        }

        if(!$this->status) {
            throw new AccountException('Class: User, status can\'t be empty', AccountBase::FAILURE );
        }
    }
}