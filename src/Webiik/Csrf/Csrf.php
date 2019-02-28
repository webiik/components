<?php
declare(strict_types=1);

namespace Webiik\Csrf;

use Webiik\Session\Session;
use Webiik\Token\Token;

class Csrf
{
    /**
     * @var Token
     */
    private $token;

    /**
     * @var Session
     */
    private $session;

    /**
     * CSRF token name
     * @var string
     */
    private $name = 'csrf-token';

    /**
     * How many CSRF tokens can user store in his/her session
     * e.g. 10 opened forms, 10 simultaneous AJAX requests
     *
     * Note:
     * This limit is here to prevent attackers to fill session with huge amount of tokens.
     * If limit is reached, method create() stops to generate new tokens and instead of it
     * returns last generated token.
     *
     * @var int
     */
    private $max = 5;

    /**
     * @param Token $token
     * @param Session $session
     */
    public function __construct(Token $token, Session $session)
    {
        $this->token = $token;
        $this->session = $session;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param int $max
     */
    public function setMax(int $max): void
    {
        $this->max = $max;
    }

    /**
     * Create CSRF token
     * @param bool $safe
     * @throws \Exception
     * @return string
     */
    public function create(bool $safe = false): string
    {
        if ($this->session->isInSession($this->name)) {
            $tokens = $this->session->getFromSession($this->name);
            if (count($tokens) == $this->max) {
                end($tokens);
                $token = (string)key($tokens);
            }
        }

        if (!isset($token)) {
            $token = $safe ? $this->token->generate(8) : $this->token->generateCheap(16);
            $this->session->addToSession($this->name, [$token => true]);
        }

        return $token;
    }

    /**
     * Check CSRF token and if it's valid, remove it from session
     * @param string $token
     * @param bool $safe
     * @return bool
     */
    public function validate(string $token, bool $safe = false): bool
    {
        $isOk = false;

        // Get tokens from session or set empty tokens array
        if ($this->session->isInSession($this->name)) {
            $tokens = $this->session->getFromSession($this->name);
        } else {
            $tokens = [];
        }

        // Check whether the token is valid. (not safe, but fast)
        if (!$safe) {
            $isOk = isset($tokens[$token]);
            unset($_SESSION[$this->name][$token]);
        }

        // Check whether the token is valid. (safe, but slower)
        if ($safe) {
            foreach ($tokens as $originalToken) {
                if ($this->token->compare($originalToken, $token)) {
                    $isOk = true;
                    break;
                }
            }
        }

        return $isOk;
    }
}
