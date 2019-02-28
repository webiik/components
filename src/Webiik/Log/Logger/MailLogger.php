<?php
declare(strict_types=1);

namespace Webiik\Log\Logger;

use Webiik\Log\Message;

class MailLogger implements LoggerInterface
{
    /**
     * Factory of underlying mail service
     * If no factory is set, PHP's mail function will be used instead
     *
     * Factory is injected with the following parameters:
     * string $to, string $from, string $subject, string $message
     *
     * @var callable|null
     */
    private $mailService;

    /**
     * Delay in minutes between sending the same log message
     * 0 = message is always sent
     * @var int
     */
    private $sendDelay = 720;

    /**
     * Here are stored hashes of sent log messages
     * @var string
     */
    private $tmpDir = '.';

    /**
     * Subject of email sent by MailLogger
     * @var string
     */
    private $subject = '';

    /**
     * Email of recipient
     * @var string
     */
    private $recipientAddress = '';

    /**
     * Email of sender
     * @var string
     */
    private $senderAddress = '';

    /**
     * Set custom mail service using the factory
     * @param callable $factory
     */
    public function setMailService(callable $factory): void
    {
        $this->mailService = $factory;
    }

    /**
     * @param string $subject
     */
    public function setSubject(string $subject): void
    {
        $this->subject = $subject;
    }

    /**
     * @param string $email
     */
    public function setTo(string $email): void
    {
        $this->recipientAddress = $email;
    }

    /**
     * @param string $email
     */
    public function setFrom(string $email): void
    {
        $this->senderAddress = $email;
    }

    /**
     * @param int $minutes
     */
    public function setDelay(int $minutes): void
    {
        $this->sendDelay = $minutes;
    }

    /**
     * @param string $tmpDir
     */
    public function setTmpDir(string $tmpDir): void
    {
        $this->tmpDir = rtrim($tmpDir, '/ ');
    }

    /**
     * @param Message $message
     */
    public function write(Message $message): void
    {
        $htmlMessage = $this->prepareHtmlMessage($message);

        // Don't send message that has been already sent or is still not expired
        if ($this->sendDelay) {
            $file = $this->tmpDir . '/' . $htmlMessage['hash'] . '.log';
            $fileExpirationTs = time() - ($this->sendDelay * 60);
            $fileLastModifiedTs = @filemtime($file);
            if ($fileLastModifiedTs && $fileLastModifiedTs > $fileExpirationTs) {
                return;
            }
            file_put_contents($file, '');
        }

        // Send message...
        $mail = $this->mailService;
        if ($mail === null) {
            // ...using the PHP's mail function
            $this->send($this->recipientAddress, $this->senderAddress, $this->subject, $htmlMessage['html']);
        } else {
            // ...using the user defined mail service
            $mail($this->recipientAddress, $this->senderAddress, $this->subject, $htmlMessage['html']);
        }
    }

    /**
     * Send HTML email in utf-8 and base64 encoding using the built-in PHP mail function
     * @param string $from
     * @param string $to
     * @param string $subject
     * @param string $message
     */
    private function send(string $from, string $to, string $subject, string $message): void
    {
        // Encode subject and message
        $subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        $message = base64_encode((string)iconv(
            (string)mb_detect_encoding($message, mb_detect_order(), true),
            'UTF-8',
            $message
        ));

        // Email header settings
        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=utf-8',
            'Content-Transfer-Encoding: base64',
            'From: ' . $from,
            'X-Mailer: PHP/' . phpversion()
        ];

        // Send message
        mail($to, $subject, $message, implode("\r\n", $headers));
    }

    /**
     * @param Message $message
     * @return array
     */
    private function prepareHtmlMessage(Message $message): array
    {
        return [
            'html' => $this->getHtmlMessage($message),
            'hash' => $this->getHtmlMessageHash($message),
        ];
    }

    /**
     * Return formatted error to html
     * @param Message $message
     * @return string
     */
    private function getHtmlMessage(Message $message): string
    {
        $html = '<b>Log Level</b><br/>';
        $html .= $message->getLevel() . '<br/><br/>';

        $html .= '<b>Date</b><br/>';
        $html .= date('Y/m/d H:i:s', $message->getTime()) . ' (' . $message->getTime() . ')<br/><br/>';

        $html .= '<b>Message</b><br/>';
        $html .= $message->getMessage() . '<br/><br/>';

        $data = $message->getData();
        if ($data) {
            $html .= '<hr/>';
            $html .= $this->arrayToHtml($message->getData());
        }

        return $html;
    }

    /**
     * @param Message $message
     * @return string
     */
    private function getHtmlMessageHash(Message $message): string
    {
        $data = json_encode($message->getData());
        return md5($message->getMessage() . $message->getLevel() . $data);
    }

    /**
     * @param array $array
     * @return string
     */
    private function arrayToHtml(array $array): string
    {
        $html = '';
        foreach ($array as $key => $val) {
            if (is_string($key)) {
                $html .= '<br/>';
                $html .= '<b>' . mb_convert_case(htmlspecialchars($key), MB_CASE_TITLE) . '</b><br/>';
            }

            if (is_string($val)) {
                $html .= htmlspecialchars($val) . '<br/>';
            }

            if (is_numeric($val)) {
                $html .= $val . '<br/>';
            }

            if (is_array($val)) {
                $html .= $this->arrayToHtml($val);
            }
        }
        return $html;
    }
}
