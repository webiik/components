<?php
declare(strict_types=1);

namespace Webiik\Translation\Parser;

use Webiik\Translation\TranslationTrait;

class Select implements ParserInterface
{
    use TranslationTrait;

    /**
     * Syntax:
     * {variableName, Select, {condition message}...}
     *
     * Example:
     * {gender, Select, {male Hello Tom!} {female Hello Kitty!}}
     *
     * @param string $varValue
     * @param string $parserString
     * @return string
     */
    public function parse($varValue, string $parserString): string
    {
        $brackets = $this->extractBrackets($parserString);
        foreach ($brackets as $bracket) {
            preg_match('/(^\w+)\s(.+)?/', $bracket, $match);
            if (isset($match[1]) && $match[1] == $varValue) {
                $parserString = isset($match[2]) ? $match[2] : '';
                break;
            }
        }

        return $parserString;
    }
}
