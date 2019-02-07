<?php
declare(strict_types=1);

namespace Webiik\Validator\Rules;

class Equal implements RuleInterface
{
    /**
     * @var
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
    public function __construct($val, $errMsg)
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
        return $input === $this->val ? true : false;
    }

    /**
     * @return string
     */
    public function getErrMessage(): string
    {
        return $this->errMsg;
    }
}
