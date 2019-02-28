<?php
declare(strict_types=1);

namespace Webiik\Validator\Rules;

interface RuleInterface
{
    /**
     * Check if $input match a rule
     * @param mixed $input
     * @return bool
     */
    public function isInputOk($input): bool;

    /**
     * Return user defined error message, when $input doesn't match a rule
     * @return string
     */
    public function getErrMessage(): string;
}
