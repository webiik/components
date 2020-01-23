<?php
declare(strict_types=1);

namespace Webiik\Translation\Parser;

use Webiik\Translation\TranslationTrait;

class Link implements ParserInterface
{
    use TranslationTrait;

    /**
     * Syntax:
     * {Link, {link text} {url} {target} {rel}}
     *
     * Example:
     * Visit the {Link, {official page} {https://www.webiik.com} {_blank} {nofollow}}.
     *
     * @param string|int $varValue
     * @param string $parserString
     * @return string
     */
    public function parse($varValue, string $parserString): string
    {
        $brackets = $this->extractBrackets($parserString);

        $text = isset($brackets[0]) ? $brackets[0] : '';
        $url = isset($brackets[1]) ? $brackets[1] : '';
        $target = isset($brackets[2]) ? ' target="' . $brackets[2] . '"' : '';
        $rel = isset($brackets[3]) ? ' rel="' . $brackets[3] . '"' : '';

        if ($url && $text) {
            $parserString = '<a href="' . $url . '"' . $target . $rel . '>' . $text . '</a>';
        }

        return $parserString;
    }
}
