<?php
declare(strict_types=1);

namespace Webiik\Translation;

trait TranslationTrait
{
    /**
     * @param string $string
     * @param bool $assoc
     * @return array
     */
    private function extractBrackets(string $string, bool $assoc = false): array
    {
        $extractions = [];
        $openingBrackets = 0;
        $closingBrackets = 0;
        $brackets = [];

        for ($i = 0; $i < strlen($string); $i++) {
            if ($string[$i] == '{') {
                $brackets[] = $i;
                $openingBrackets++;
            }
            if ($string[$i] == '}') {
                $brackets[] = $i;
                $closingBrackets++;
            }

            if ($openingBrackets == $closingBrackets && $closingBrackets > 0) {
                $extraction = substr($string, $brackets[0] + 1, $brackets[count($brackets) - 1] - $brackets[0] - 1);
                if ($assoc) {
                    $extractions[$extraction] = 1;
                } else {
                    $extractions[] = $extraction;
                }
                $openingBrackets = 0;
                $closingBrackets = 0;
                $brackets = [];
            }
        }

        return $extractions;
    }
}
