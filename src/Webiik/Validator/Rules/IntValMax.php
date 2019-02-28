<?php
declare(strict_types=1);

namespace Webiik\Validator\Rules;

class IntValMax implements RuleInterface
{
    /**
     * @var int
     */
    private $max;

    /**
     * @var string
     */
    private $errMsg;

    /**
     * @param int $max
     * @param string $errMsg
     */
    public function __construct(int $max, string $errMsg = '')
    {
        $this->max = $max;
        $this->errMsg = $errMsg;
    }

    /**
     * @param int $input
     * @return bool
     */
    public function isInputOk($input): bool
    {
        return $input <= $this->max;
    }

    /**
     * @return string
     */
    public function getErrMessage(): string
    {
        return $this->errMsg;
    }
}
