<?php
declare(strict_types=1);

namespace Webiik\Validator\Rules;

class Regex implements RuleInterface
{
    /**
     * @var string
     */
    private $regex;

    /**
     * @var string
     */
    private $errMsg;

    /**
     * @param string $regex
     * @param string $errMsg
     */
    public function __construct(string $regex, string $errMsg = '')
    {
        $this->regex = $regex;
        $this->errMsg = $errMsg;
    }

    /**
     * @param string $input
     * @return bool
     */
    public function isInputOk($input): bool
    {
        return (bool)preg_match($this->regex, $input);
    }

    /**
     * @return string
     */
    public function getErrMessage(): string
    {
        return $this->errMsg;
    }
}
