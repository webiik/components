<?php
declare(strict_types=1);

namespace Webiik\Mail\Mailer;

use Webiik\Container\Container;
use Webiik\Log\Log;
use Webiik\Mail\Message;

class PHPMailer implements MailerInterface
{
    /**
     * @var \PHPMailer\PHPMailer\PHPMailer
     */
    private $mailer;

    /**
     * @var Container
     */
    private $container;

    public function __construct(\PHPMailer\PHPMailer\PHPMailer $mailer, Container $container)
    {
        $this->mailer = $mailer;
        $this->container = $container;
    }

    /**
     * Send all messages
     * @param array $messages
     * @return mixed|void
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function send(array $messages)
    {
        foreach ($messages as $message) {
            /** @var Message $message */
            $this->setCharset($message);
            $this->setPriority($message);
            $this->setFrom($message);
            $this->setReplyTo($message);
            $this->setBounceAddress($message);
            $this->addTo($message);
            $this->addCc($message);
            $this->addBcc($message);
            $this->setSubject($message);
            $this->setBody($message);
            $this->setAlternativeBody($message);
            $this->addDynamicAttachment($message);
            $this->addFileAttachment($message);
            $this->addDynamicEmbed($message);
            $this->addFileEmbed($message);
            if (!$this->mailer->send() && $this->container->isIn('Webiik\Log\Log')) {
                $this->logError($message);
            }
            $this->mailer->clearAllRecipients();
            $this->mailer->clearAttachments();
        }
    }

    /**
     * @return \PHPMailer\PHPMailer\PHPMailer
     */
    public function core(): \PHPMailer\PHPMailer\PHPMailer
    {
        return $this->mailer;
    }

    /**
     * @param Message $message
     */
    public function setCharset(Message $message): void
    {
        $this->mailer->CharSet = $message->getCharset();
    }

    /**
     * @param Message $message
     */
    public function setPriority(Message $message): void
    {
        $priority = $message->getPriority();
        if ($priority) {
            $this->mailer->Priority = $priority;
        }
    }

    /**
     * Add body
     * @param Message $message
     */
    public function setBody(Message $message): void
    {
        $body = $message->getBody();
        if ($body) {
            $this->mailer->Body = $body[0];
            $this->mailer->ContentType = $body[1];
        }
    }

    /**
     * Add body
     * @param Message $message
     */
    public function setAlternativeBody(Message $message): void
    {
        $this->mailer->AltBody = $message->getAlternativeBody();
    }

    /**
     * Set from email and name
     * @param Message $message
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function setFrom(Message $message): void
    {
        $this->mailer->setFrom(...$message->getFrom());
    }

    /**
     * @param Message $message
     */
    public function setReplyTo(Message $message): void
    {
        foreach ($message->getReplyTo() as $recipient) {
            $this->mailer->addReplyTo(...$recipient);
        }
    }

    /**
     * @param Message $message
     */
    public function setBounceAddress(Message $message): void
    {
        $this->mailer->Sender = $message->getBounceAddress();
    }

    /**
     * Set subject
     * @param Message $message
     */
    public function setSubject(Message $message): void
    {
        $this->mailer->Subject = $message->getSubject();
    }

    /**
     * Add recipients
     * @param Message $message
     */
    public function addTo(Message $message): void
    {
        foreach ($message->getTo() as $recipient) {
            $this->mailer->addAddress(...$recipient);
        }
    }

    /**
     * Add CC recipients
     * @param Message $message
     */
    public function addCc(Message $message): void
    {
        foreach ($message->getCc() as $recipient) {
            $this->mailer->addCC(...$recipient);
        }
    }

    /**
     * Add BCC recipients
     * @param Message $message
     */
    public function addBcc(Message $message): void
    {
        foreach ($message->getBcc() as $recipient) {
            $this->mailer->addBCC(...$recipient);
        }
    }

    /**
     * Attach dynamic content to message
     * @param Message $message
     */
    public function addDynamicAttachment(Message $message): void
    {
        foreach ($message->getDynamicAttachments() as $attachment) {
            $this->mailer->addStringAttachment(
                $attachment[0],
                $attachment[1],
                $this->mailer::ENCODING_BASE64,
                $attachment[2]
            );
        }
    }

    /**
     * Attach files to message
     * @param Message $message
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function addFileAttachment(Message $message): void
    {
        foreach ($message->getFileAttachments() as $attachment) {
            $this->mailer->addAttachment(
                $attachment[0],
                $attachment[1],
                $this->mailer::ENCODING_BASE64,
                $attachment[2]
            );
        }
    }

    /**
     * Embed dynamic content to message (usually images)
     * @param Message $message
     */
    public function addDynamicEmbed(Message $message): void
    {
        foreach ($message->getDynamicEmbeds() as $embed) {
            $this->mailer->addStringEmbeddedImage(
                $embed[0],
                $embed[1],
                $embed[2],
                $this->mailer::ENCODING_BASE64,
                $embed[3]
            );
        }
    }

    /**
     * Embed files to message (usually images)
     * @param Message $message
     */
    public function addFileEmbed(Message $message): void
    {
        foreach ($message->getFileEmbeds() as $embed) {
            $this->mailer->addEmbeddedImage(
                $embed[0],
                $embed[1],
                $embed[2],
                $this->mailer::ENCODING_BASE64,
                $embed[3]
            );
        }
    }

    /**
     * @param Message $message
     */
    public function logError(Message $message): void
    {
        /** @var Log $log */
        $log = $this->container->get('Webiik\Log\Log');
        $logMsg = 'Unable to send "{subject}" to one among: {email}';

        // Create string with all recipients of this message
        $emails = '';
        $recipients = array_merge($message->getTo(), $message->getCc(), $message->getBcc());
        foreach ($recipients as $recipient) {
            $emails .= $recipient[0] . ', ';
        }
        $emails = rtrim($emails, ', ');

        // Log error
        $log->log('mail', $logMsg, [
            'subject' => $message->getSubject(),
            'email' => $emails,
        ]);
    }
}
