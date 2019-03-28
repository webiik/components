<?php
declare(strict_types=1);

namespace Webiik\Account;

class AccountException extends \Exception
{
    /**
     * @var array
     */
    private $validationResult;

    /**
     * AccountException constructor.
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     * @param array $validationResult
     */
    public function __construct($message = '', $code = 0, \Throwable $previous = null, array $validationResult = [])
    {
        parent::__construct($message, $code, $previous);
        $this->validationResult = $validationResult;
    }

    /**
     * @return array
     */
    public function getValidationResult(): array
    {
        return $this->validationResult;
    }
}
