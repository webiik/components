<?php
declare(strict_types=1);

namespace Webiik\Validator\Rules;

class IntVal implements RuleInterface
{
    /**
     * @var int
     */
    private $min;

    /**
     * @var int
     */
    private $max;

    /**
     * @var string
     */
    private $errMsg;

    /**
     * @param int $min
     * @param int $max
     * @param string $errMsg
     */
    public function __construct(int $min, int $max, string $errMsg = '')
    {
        $this->min = $min;
        $this->max = $max;
        $this->errMsg = $errMsg;
    }

    /**
     * @param int $input
     * @return bool
     */
    public function isInputOk($input): bool
    {
        return $input >= $this->min && $input <= $this->max;
    }

    /**
     * @return string
     */
    public function getErrMessage(): string
    {
        return $this->errMsg;
    }
}
