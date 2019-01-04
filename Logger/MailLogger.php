<?php
declare(strict_types=1);

namespace Webiik\Log\Logger;

use mysql_xdevapi\Exception;
use Webiik\Container\Container;
use Webiik\Log\Record;
use Webiik\Mail\Mail;

class MailLogger implements LoggerInterface
{
    /**
     * @var Container
     */
    private $container;

    /**
     * Name of Webiik\Mail in Container
     * @var string
     */
    private $mailServiceName = '';

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
     * @param string $mailServiceName
     * @param Container $container
     */
    public function setMailService(string $mailServiceName, Container $container): void
    {
        $this->mailServiceName = $mailServiceName;
        $this->container = $container;
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
     * @param array $records
     */
    public function process(array $records): void
    {
        $this->send($this->prepareHtmlMessages($records));
    }

    /**
     * Send log messages by email
     * @param array $htmlMessages
     */
    private function send(array $htmlMessages): void
    {
        /** @var Mail $mail */
        $mail = $this->container->get($this->mailServiceName);

        // Prepare log messages to send
        $messages = [];
        foreach ($htmlMessages as $htmlMessageArr) {
            // Skip messages that has been already sent and are still not expired
            if ($htmlMessageArr[1]) {
                $file = $this->tmpDir . '/' . $htmlMessageArr[1] . '.log';
                $fileExpirationTs = time() - ($this->sendDelay * 60);
                $fileLastModifiedTs = @filemtime($file);
                if ($fileLastModifiedTs && $fileLastModifiedTs > $fileExpirationTs) {
                    continue;
                }
                file_put_contents($file, '');
            }

            // Prepare message with log to send
            $message = $mail->createMessage();
            $message->setSubject($this->subject);
            $message->setFrom($this->senderAddress);
            $message->addTo($this->recipientAddress);
            $message->setBody($htmlMessageArr[0]);
            $messages[] = $message;
        }

        $mail->send($messages);
    }

    /**
     * @param array $records
     * @return array
     */
    private function prepareHtmlMessages(array $records): array
    {
        $htmlMessages = [];

        foreach ($records as $record) {
            /** @var Record $record */
            $htmlMessages[] = [
                $this->getHtmlMessage($record),
                $this->getHtmlMessageHash($record),
            ];
        }

        return $htmlMessages;
    }

    /**
     * Return formatted error to html
     * @param Record $record
     * @return string
     */
    private function getHtmlMessage(Record $record): string
    {
        $html = '<b>Log Level</b><br/>';
        $html .= $record->getLevel() . '<br/><br/>';

        $html .= '<b>Date</b><br/>';
        $html .= date('Y/m/d H:i:s', $record->getTime()) . ' (' . $record->getTime() . ')<br/><br/>';

        $html .= '<b>Message</b><br/>';
        $html .= $record->getMessage() . '<br/><br/>';

        $data = $record->getData();
        if ($data) {
            $html .= '<hr/>';
            $html .= $this->arrayToHtml($record->getData());
        }

        return $html;
    }

    /**
     * @param Record $record
     * @return string
     */
    private function getHtmlMessageHash(Record $record): string
    {
        $data = '';
        if ($this->sendDelay && $record->getData()) {
            $data = md5(json_encode($record->getData()));
        }
        return md5($record->getMessage() . $record->getLevel() . $data);
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
