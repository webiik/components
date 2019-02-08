<?php
declare(strict_types=1);

namespace Webiik\Validator\Rules;

class IsObject implements RuleInterface
{
    /**
     * @var string
     */
    private $errMsg;

    /**
     * @param string $errMsg
     */
    public function __construct(string $errMsg = '')
    {
        $this->errMsg = $errMsg;
    }

    /**
     * @param mixed $input
     * @return bool
     */
    public function isInputOk($input): bool
    {
        return is_object($input);
    }

    /**
     * @return string
     */
    public function getErrMessage(): string
    {
        return $this->errMsg;
    }
}
