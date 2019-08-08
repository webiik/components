<?php
declare(strict_types=1);

namespace Webiik\Validator\Rules;

class IsRequired implements RuleInterface
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
        return $input ? true : false;
    }

    /**
     * @return string
     */
    public function getErrMessage(): string
    {
        return $this->errMsg;
    }
}
