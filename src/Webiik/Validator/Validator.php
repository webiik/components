<?php
declare(strict_types=1);

namespace Webiik\Validator;

use Webiik\Validator\Rules\RuleInterface;

class Validator
{
    /**
     * @var array
     */
    private $inputs = [];

    /**
     * Add an input to an array of inputs for validation
     * @param mixed $input Input value to validate
     * @param callable $rules MUST return array of RuleInterface implementations
     * @param string $name Input name
     * @param bool $isRequired Is input required or optional?
     */
    public function addInput($input, callable $rules, string $name = '', bool $isRequired = false): void
    {
        if ($name) {
            $this->inputs[$name] = [
                'i' => $input,
                'r' => $rules,
                'q' => $isRequired,
            ];
        } else {
            $this->inputs[] = [
                'i' => $input,
                'r' => $rules,
                'q' => $isRequired,
            ];
        }
    }

    /**
     * @param bool $testAllRules Indicates if unfulfilled rule stops next rules checking
     * @return array Array of invalid inputs sorted by input index
     */
    public function validate($testAllRules = false): array
    {
        $invalid = [];
        foreach ($this->inputs as $key => $input) {
            // Don't check empty optional inputs
            if (!$input['i'] && !$input['q']) {
                continue;
            }
            foreach ($input['r']() as $rule) {
                /**@var RuleInterface $rule */
                if (!$rule->isInputOk($input['i'])) {
                    $invalid[$key][] = $rule->getErrMessage();
                    if (!$testAllRules) {
                        break;
                    }
                }
            }
        }
        return $invalid;
    }
}
