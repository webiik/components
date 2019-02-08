<?php
declare(strict_types=1);

namespace Webiik\Validator\Rules;

class StrLenMin implements RuleInterface
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
     * @param string $input
     * @return bool
     */
    public function isInputOk($input): bool
    {
        $inputLength = mb_strlen($input);
        return $inputLength >= $this->min;
    }

    /**
     * @return string
     */
    public function getErrMessage(): string
    {
        return $this->errMsg;
    }
}
