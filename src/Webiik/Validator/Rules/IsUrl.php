<?php
declare(strict_types=1);

namespace Webiik\Validator\Rules;

class IsUrl implements RuleInterface
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
        $isOk = false;

        if (filter_var($input, FILTER_VALIDATE_URL) !== false) {
            $isOk = true;
        }

        return $isOk;
    }

    /**
     * @return string
     */
    public function getErrMessage(): string
    {
        return $this->errMsg;
    }
}
