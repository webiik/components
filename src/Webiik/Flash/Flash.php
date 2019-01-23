<?php
declare(strict_types=1);

namespace Webiik\Flash;

use Webiik\Session\Session;

class Flash
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var array
     */
    private $messages = [];

    /**
     * @var string
     */
    private $lang = 'en';

    /**
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;

        // Move current lang messages from session to current messages array
        $messagesFromPrevReq = $this->getFlashNext();
        foreach ($messagesFromPrevReq as $type => $messages) {
            foreach ($messages as $message) {
                $this->addFlashCurrent($type, $message);
            }
        }
    }

    /**
     * Set lang of flash messages
     * @param string $lang
     */
    public function setLang(string $lang): void
    {
        $this->lang = $lang;
    }

    /**
     * Add flash message to be displayed in current request
     * @param string $type
     * @param string $message
     * @param array $context
     */
    public function addFlashCurrent(string $type, string $message, array $context = []): void
    {
        if ($context) {
            $message = $this->parseContext($message, $context);
        }
        $this->messages[$this->lang]['now'][$type][] = $message;
    }

    /**
     * Add flash message to be displayed in further request
     * @param string $type
     * @param string $message
     * @param array $context
     */
    public function addFlashNext(string $type, string $message, array $context = []): void
    {
        if ($context) {
            $message = $this->parseContext($message, $context);
        }
        $this->session->sessionStart();
        $_SESSION['messages'][$this->lang][$type][] = $message;
    }

    /**
     * Get flash messages for current lang and request
     * @return array
     */
    public function getFlashes(): array
    {
        return isset($this->messages[$this->lang], $this->messages[$this->lang]['now']) ? $this->messages[$this->lang]['now'] : [];
    }

    /**
     * Get and remove all current lang flash messages set in session
     * @return array
     */
    private function getFlashNext(): array
    {
        $messages = [];
        if ($this->session->isInSession('messages')) {
            $messages = $this->session->getFromSession('messages');
            unset($_SESSION['messages'][$this->lang]);
        }
        return isset($messages[$this->lang]) ? $messages[$this->lang] : [];
    }

    /**
     * @param string $message
     * @param array $context
     * @return string
     */
    private function parseContext(string $message, array $context): string
    {
        $replace = [];
        foreach ($context as $key => $val) {
            $val = is_numeric($val) ? $val : htmlspecialchars($val);
            $replace['{' . $key . '}'] = $val;
        }
        return strtr($message, $replace);
    }
}
