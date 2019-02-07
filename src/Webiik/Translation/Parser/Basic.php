<?php
declare(strict_types=1);

namespace Webiik\Translation\Parser;

class Basic implements ParserInterface
{
    /**
     * Syntax:
     * {varName}
     *
     * Example:
     * Hello {name}!
     *
     * @param string|int $varValue
     * @param string $parserString
     * @return string
     */
    public function parse($varValue, string $parserString): string
    {
        return $varValue;
    }
}
