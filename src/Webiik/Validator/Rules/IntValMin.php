<?php
declare(strict_types=1);

namespace Webiik\Validator\Rules;

class IntValMin implements RuleInterface
{
    /**
     * @var int
     */
    private $min;

    /**
     * @var string
     */
    private $errMsg;

    /**
     * @param int $min
     * @param string $errMsg
     */
    public function __construct(int $min, string $errMsg = '')
    {
        $this->min = $min;
        $this->errMsg = $errMsg;
    }

    /**
     * @param int $input
     * @return bool
     */
    public function isInputOk($input): bool
    {
        return $input >= $this->min;
    }

    /**
     * @return string
     */
    public function getErrMessage(): string
    {
        return $this->errMsg;
    }
}
