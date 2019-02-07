<?php
declare(strict_types=1);

namespace Webiik\Translation\Parser;

use Webiik\Translation\TranslationTrait;

class Select implements ParserInterface
{
    use TranslationTrait;

    /**
     * Syntax:
     * {variableName, Select, =string {message}...}
     *
     * Example:
     * {gender, Select, =male {He} =female {She}} likes vanilla ice cream.
     *
     * @param string $varValue
     * @param string $parserString
     * @return string
     */
    public function parse($varValue, string $parserString): string
    {
        // Get values of all conditions
        preg_match_all('/=(\w+)/', $parserString, $matches);

        $matchedIndex = null;

        // Find the condition that matches $varValue
        foreach ($matches[1] as $index => $match) {
            if ($varValue == $match) {
                $matchedIndex = $index;
                break;
            }
        }

        if ($matchedIndex !== null) {
            $brackets = $this->extractBrackets($parserString);
            $parserString = $brackets[$matchedIndex];
        }

        return $parserString;
    }
}
