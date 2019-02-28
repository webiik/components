<?php
declare(strict_types=1);

namespace Webiik\Validator\Rules;

class IsEmail implements RuleInterface
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
     * @param string $input
     * @return bool
     */
    public function isInputOk($input): bool
    {
        return (bool)preg_match('/^[\w\.\_\-]+@[\w\.\_\-]+\.[a-z]{2,63}$/ui', $input);
    }

    /**
     * @return string
     */
    public function getErrMessage(): string
    {
        return $this->errMsg;
    }
}
