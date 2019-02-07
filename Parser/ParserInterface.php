<?php
declare(strict_types=1);

namespace Webiik\Translation\Parser;

interface ParserInterface
{
    /**
     * @param string|int $varValue
     * @param string $parserString
     * @return string
     */
    public function parse($varValue, string $parserString): string;
}
