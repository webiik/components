<?php
declare(strict_types=1);

namespace Webiik\Translation\Parser;

use Webiik\Translation\TranslationTrait;

class Plural implements ParserInterface
{
    use TranslationTrait;

    /**
     * Syntax:
     * {variableName, Plural, {condition message}...}
     *
     * Example:
     * {numCats, Plural, {-2-0 No cats.} {1 One cat.} {2+ {numCats} cats.}}
     *
     * Available conditions:
     * num       Equal
     * num-num   Range between num and num incl.
     * num+      Higher than num incl.
     * num-      Lower than num incl.
     *
     * @param string|int $varValue
     * @param string $parserString
     * @return string
     */
    public function parse($varValue, string $parserString): string
    {
        $brackets = $this->extractBrackets($parserString);
        foreach ($brackets as $bracket) {
            preg_match('/^(-?\d+)([-\+]?)(-?\d*)\s(.+)?/', $bracket, $match);
            if (isset($match[1])) {
                if ($match[1] == $varValue) {
                    // Exact value
                    $parserString = isset($match[4]) ? $match[4] : '';
                    break;
                } elseif ($match[2] == '-' && $match[3]) {
                    // Range
                    if ($varValue >= $match[1] && $varValue <= $match[3]) {
                        $parserString = isset($match[4]) ? $match[4] : '';
                        break;
                    }
                } elseif ($match[2] == '+' && !$match[3]) {
                    // Equal or higher than
                    if ($varValue >= $match[1]) {
                        $parserString = isset($match[4]) ? $match[4] : '';
                        break;
                    }
                } elseif ($match[2] == '-' && !$match[3]) {
                    // Equal or lower than
                    if ($varValue <= $match[1] * -1) {
                        $parserString = isset($match[4]) ? $match[4] : '';
                        break;
                    }
                }
            }
        }

        return $parserString;
    }
}
