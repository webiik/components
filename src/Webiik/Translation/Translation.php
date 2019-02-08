<?php
declare(strict_types=1);

namespace Webiik\Translation;

use Webiik\Arr\Arr;
use Webiik\Translation\Parser\ParserInterface;

class Translation
{
    use TranslationTrait;

    /**
     * @var Arr
     */
    private $arr;

    /**
     * Array with all available translations
     * @var array
     */
    private $translation = [];

    /**
     * Current language
     * @var string
     */
    private $lang = 'en';

    /**
     * Missing data: keys and contexts, it can be filled by get(), getAll()
     * @var array
     */
    private $missing = [];

    /**
     * Instances of parsers
     * @var array
     */
    private $parsers = [];

    /**
     * @param Arr $arr
     */
    public function __construct(Arr $arr)
    {
        $this->arr = $arr;
    }

    /**
     * Set current lang of translation
     * @param string $lang
     */
    public function setLang(string $lang): void
    {
        $this->lang = $lang;
    }

    /**
     * Add translation by key
     * @param string $key Allows to use dot notation.
     * @param string $val
     */
    public function add(string $key, string $val): void
    {
        $this->arr->set($key, $val, $this->translation[$this->lang]);
    }

    /**
     * Add translations
     *
     * Note about resolving the key conflicts:
     *
     * Arrays values
     * New value that is an array is merged with old value that is an array.
     * If array key is a string, value of the new key replaces value of the old key.
     *
     * Mixed values
     * New value that is a different type than old value, replaces old value.
     * e.g. New string value replaces old array value and vice-versa.
     *
     * @param array $translation
     * @param bool $context
     */
    public function addArr(array $translation, &$context = false): void
    {
        if (!isset($this->translation[$this->lang])) {
            $this->translation[$this->lang] = [];
        }

        if ($context === false) {
            $context = &$this->translation[$this->lang];
        }

        foreach ($translation as $ikey => $val) {
            if (is_array($val)) {
                if (isset($context[$ikey]) && !is_array($context[$ikey])) {
                    // If new value is an array and original value already exists and it's
                    // not an array - It's necessary to re-type original value to the array
                    // to allow its update
                    $context[$ikey] = [];
                }
                $this->addArr($val, $context[$ikey]);
            } elseif (is_string($ikey)) {
                // If key is a string, it means it comes from associative array and
                // its value will be updated with new value.
                $context[$ikey] = $val;
            } else {
                // If key is not a string, it means it comes from sequential array and
                // its value will be added to that array.
                $context[] = $val;
            }
        }
    }

    /**
     * Get translation by key and add missing keys, contexts to this->missing array
     * @param string $key
     * @param array|null $context
     * @return array|string
     */
    public function get(string $key, $context = null)
    {
        if (!$this->arr->isIn($key, $this->translation[$this->lang])) {
            $this->addMissingKey($key);
            return '';
        }

        if ($context === null) {
            return $this->arr->get($key, $this->translation[$this->lang]);
        }

        return $this->getParsed($key, $this->arr->get($key, $this->translation[$this->lang]), $context);
    }

    /**
     * Get all translations and add missing contexts to this->missing array
     * @param array|null $context
     * @return array
     */
    public function getAll($context = null): array
    {
        if (!isset($this->translation[$this->lang])) {
            return [];
        }

        if ($context === null) {
            return $this->translation[$this->lang];
        }

        return $this->getParsed('', $this->translation[$this->lang], $context);
    }

    /**
     * Get missing keys and contexts
     * It's available after calling get(), getAll()
     * @return array
     */
    public function getMissing(): array
    {
        return isset($this->missing[$this->lang]) ? $this->missing[$this->lang] : [];
    }

    /**
     * Parse translation and add missing contexts
     * @param string $key
     * @param string|array $input
     * @param array $context
     * @return string|array
     */
    private function getParsed(string $key, $input, array $context)
    {
        if (is_array($input)) {
            // Translation input is an array, respond with array
            foreach ($input as $ikey => $val) {
                $newKey = $key ? $key . '.' . $ikey : $ikey;
                $res[$ikey] = $this->getParsed($newKey, $val, isset($context[$ikey]) ? $context[$ikey] : []);
            }
        } elseif (is_string($input)) {
            // Translation input is a string, respond with string
            $res = $input;

            // String may contain some brackets to parse and replace, check it...
            $parsedBrackets = [];
            $missingContext = [];

            $brackets = $this->extractBrackets($input, true);
            foreach ($brackets as $bracket => $val) {
                // Get from bracket: var name, parser name and parser string
                preg_match('/^([^,]+),?\s?(\w+)?,?\s?(.*)$/', $bracket, $match);

                // If context contains var from bracket, parse bracket
                if (isset($context[$match[1]]) && !is_array($context[$match[1]])) {
                    // If parser name is not set, it means it's simple bracket, use Basic parser
                    $match[2] = $match[2] ? $match[2] : 'Basic';
                    $parser = $this->getParser($match[2]);
                    $parsedBracket = $parser->parse($context[$match[1]], $match[3]);

                    // Parse basic brackets inside advanced brackets
                    if ($match[2] != 'Basic') {
                        $parsedBracket = $this->parseBasicBrackets(
                            $key,
                            $parsedBracket,
                            $context,
                            $missingContext
                        );
                    }

                    $parsedBrackets['{' . $bracket . '}'] = $parsedBracket;
                } else {
                    // Add missing context
                    $missingContext[$key][] = $match[1];
                }
            }

            // Replace brackets with parsed brackets
            if ($parsedBrackets) {
                $res = strtr($input, $parsedBrackets);
            }

            // Add missing context to $this->missing array
            foreach ($missingContext as $ikey => $values) {
                $this->addMissingContext($ikey, $values);
            }
        }

        return isset($res) ? $res : '';
    }

    /**
     * Parse simple brackets and add missing context
     * @param string $key
     * @param string $string
     * @param array $context
     * @param array $missingContext
     * @return string
     */
    private function parseBasicBrackets(string $key, string $string, array $context, array &$missingContext)
    {
        $parsedBrackets = [];

        preg_match_all('/{([^}]+)}/', $string, $matches);
        foreach ($matches[1] as $match) {
            if (isset($context[$match])) {
                $parsedBrackets['{' . $match . '}'] = $context[$match];
            } else {
                $missingContext[$key][] = $match;
            }
        }

        if ($parsedBrackets) {
            $string = strtr($string, $parsedBrackets);
        }

        return $string;
    }

    /**
     * @param string $key
     */
    private function addMissingKey(string $key): void
    {
        $this->missing[$this->lang]['keys'][] = $key;
    }

    /**
     * @param string $key
     * @param array $context
     */
    private function addMissingContext(string $key, array $context = []): void
    {
        $this->missing[$this->lang]['context'][$key] = $context;
    }

    /**
     * Get parser instance
     * @param string $className
     * @return ParserInterface
     */
    private function getParser(string $className): ParserInterface
    {
        if (isset($this->parsers[$className])) {
            $parser = $this->parsers[$className];
        } else {
            $parserName = '\Webiik\Translation\Parser\\' . $className;
            $parser = new $parserName();
            $this->parsers[$className] = $parser;
        }
        return $parser;
    }
}
