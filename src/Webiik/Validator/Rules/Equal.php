<?php
declare(strict_types=1);

namespace Webiik\Validator\Rules;

class Equal implements RuleInterface
{
    /**
     * @var mixed
     */
    private $val;

    /**
     * @var string
     */
    private $errMsg;

    /**
     * @param mixed $val
     * @param string $errMsg
     */
    public function __construct($val, string $errMsg = '')
    {
        $this->val = $val;
        $this->errMsg = $errMsg;
    }

    /**
     * @param mixed $input
     * @return bool
     */
    public function isInputOk($input): bool
    {
        return $input === $this->val;
    }

    /**
     * @return string
     */
    public function getErrMessage(): string
    {
        return $this->errMsg;
    }
}
