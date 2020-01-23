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
     * Dependencies of parsers
     * @var array
     */
    private $injections = [];

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
     * Inject dependencies to specific parser
     * @param string $parserClassName
     * @param TranslationInjector $injector
     */
    public function inject(string $parserClassName, TranslationInjector $injector): void
    {
        $this->injections[$parserClassName] = $injector;
    }

    /**
     * Add translation by key
     * @param string $key Allows to use dot notation.
     * @param string $val
     */
    public function add(string $key, string $val): void
    {
        if (!isset($this->translation[$this->lang])) {
            $this->translation[$this->lang] = [];
        }

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
     * Get translation by key. Add missing keys, contexts to this->missing array
     * @param string $key
     * @param array|bool|null $parse
     * @return array|string
     */
    public function get(string $key, $parse = null)
    {
        if (!$this->arr->isIn($this->lang . '.' . $key, $this->translation)) {
            $this->addMissingKey($key);
            return '';
        }

        if ($parse === null || $parse === false) {
            return $this->arr->get($key, $this->translation[$this->lang]);
        }

        return $this->getParsed($key, $this->arr->get($key, $this->translation[$this->lang]), $parse);
    }

    /**
     * Get all translations and add missing contexts to this->missing array
     * @param array|bool|null $parse
     * @return array
     */
    public function getAll($parse = null): array
    {
        if (!isset($this->translation[$this->lang])) {
            return [];
        }

        if ($parse === null || $parse === false) {
            return $this->translation[$this->lang];
        }

        return $this->getParsed('', $this->translation[$this->lang], $parse);
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
     * @param bool|array $context
     * @return string|array
     */
    private function getParsed(string $key, $input, $context)
    {
        if ($context === true) {
            $context = [];
        }

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
                // Get from bracket: var name, parser class name and parser string
                preg_match('/^([^,]+),?\s?(\w+)?,?\s?(.*)$/', $bracket, $match);

                // Determine bracket style...
                if ($match[0] && $match[1]) {
                    if (!$match[2]) {
                        if (!$match[3]) {
                            // Simple bracket
                            // {varName}
                            // varName - $match[0], $match[1]

                            // If context contains varName from bracket, parse bracket
                            if (isset($context[$match[1]]) && !is_array($context[$match[1]])) {
                                $parser = $this->getParser('Basic');
                                $parsedBrackets['{' . $bracket . '}'] = $parser->parse($context[$match[1]], '');
                            } else {
                                // Add missing context
                                $missingContext[$key][] = $match[1];
                            }

                        } else {
                            // Short bracket
                            // {parserClassName, stringToParse}
                            // parserClassName - $match[1]
                            // stringToParse - $match[3]

                            // If context contains varName from bracket, parse bracket
                            $parser = $this->getParser($match[1]);
                            $parsedBracket = $parser->parse('', $match[3]);

                            // Parse basic brackets inside advanced brackets
                            $parsedBracket = $this->parseBasicBrackets(
                                $key,
                                $parsedBracket,
                                $context,
                                $missingContext
                            );

                            $parsedBrackets['{' . $bracket . '}'] = $parsedBracket;
                        }

                    } else {
                        if ($match[3]) {
                            // Standard bracket
                            // {varName, parserClassName, stringToParse}
                            // varName - $match[1]
                            // parserClassName - $match[2]
                            // stringToParse - $match[3]

                            // If context contains varName from bracket, parse bracket
                            if (isset($context[$match[1]]) && !is_array($context[$match[1]])) {
                                $parser = $this->getParser($match[2]);
                                $parsedBracket = $parser->parse($context[$match[1]], $match[3]);

                                // Parse basic brackets inside advanced brackets
                                $parsedBracket = $this->parseBasicBrackets(
                                    $key,
                                    $parsedBracket,
                                    $context,
                                    $missingContext
                                );

                                $parsedBrackets['{' . $bracket . '}'] = $parsedBracket;

                            } else {
                                // Add missing context
                                $missingContext[$key][] = $match[1];
                            }
                        }
                    }
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
            // Get instantiated parser
            $parser = $this->parsers[$className];
        } else {
            // Instantiate parser
            $parserName = '\Webiik\Translation\Parser\\' . $className;

            if (isset($this->injections[$className])) {
                // Parser with dependencies
                $parser = new $parserName(...$this->injections[$className]());
            } else {
                // Parser without dependencies
                $parser = new $parserName();
            }

            $this->parsers[$className] = $parser;
        }
        return $parser;
    }
}
