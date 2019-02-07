<?php
declare(strict_types=1);

namespace Webiik\Translation\Parser;

use Webiik\Translation\TranslationTrait;

class Plural implements ParserInterface
{
    use TranslationTrait;

    /**
     * Syntax:
     * {variableName, Plural, =int {message}...}
     *
     * Example:
     * {numCats, Plural, =0 {No cat has} =1 {One cat has} =2+ {{numCats} cats have}} birthday.
     *
     * Available conditions:
     * =num       Equal
     * =num-num   Range between num and num incl.
     * =num+      Higher than num incl.
     * =num-      Lower than num incl.
     *
     * Note: num can have also a negative value
     *
     * @param string|int $varValue
     * @param string $parserString
     * @return string
     */
    public function parse($varValue, string $parserString): string
    {
        // Get values of all conditions
        preg_match_all('/=(-?\d+)([-\+]?)(-?\d*)/', $parserString, $matches);

        $matchedIndex = null;

        // Find the condition that matches $varValue
        foreach ($matches[1] as $index => $match) {
            if (!$matches[2][$index]) {
                // Exact value
                if ($varValue == $match) {
                    $matchedIndex = $index;
                    break;
                }
            } elseif ($matches[2][$index] == '-' && $matches[3][$index] !== null) {
                // Range
                if ($varValue >= $match && $varValue <= $matches[3][$index]) {
                    $matchedIndex = $index;
                    break;
                }
            } elseif ($matches[2][$index] == '+') {
                // Equal or higher than
                if ($varValue >= $match) {
                    $matchedIndex = $index;
                    break;
                }
            } elseif ($matches[2][$index] == '-' && $matches[3][$index] === null) {
                // Equal or lower than
                if ($varValue <= $match) {
                    $matchedIndex = $index;
                    break;
                }
            }
        }

        if ($matchedIndex !== null) {
            $brackets = $this->extractBrackets($parserString);
            $parserString = $brackets[$matchedIndex];
        }

        return $parserString;
    }
}
