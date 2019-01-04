<?php
declare(strict_types=1);

namespace Webiik\Mail\Mailer;

use Webiik\Mail\Message;

class SwiftMailer implements MailerInterface
{
    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var \Swift_Message
     */
    private $swiftMessage;

    /**
     * @param \Swift_Mailer $mailer
     */
    public function __construct(\Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    public function core(): \Swift_Mailer
    {
        return $this->mailer;
    }

    public function send(array $messages): array
    {
        $undelivered = [];

        foreach ($messages as $message) {
            /** @var Message $message */

            $this->swiftMessage = new \Swift_Message();

            $this->setCharset($message->getCharset());
            $this->setPriority($message->getPriority());
            $this->setFrom(...$message->getFrom());
            $this->setBounceAddress($message->getBounceAddress());

            $this->addReplyToAddresses($message);
            $this->addCCs($message);
            $this->addBCCs($message);

            $this->setSubject($message->getSubject());
            $this->setBody(...$message->getBody());
            $this->setAlternativeBody($message->getAlternativeBody());

            $this->addDynamicAttachments($message);
            $this->addFileAttachments($message);
            $this->addDynamicEmbeds($message);
            $this->addFileEmbeds($message);

            foreach ($message->getTo() as $recipient) {
                $this->addTo(...$recipient);
            }

            $failedRecipients = [];
            $this->mailer->send($this->swiftMessage, $failedRecipients);

            if ($failedRecipients) {
                $undelivered = array_merge($undelivered, $failedRecipients);
            }
        }

        return $undelivered;
    }

    public function setCharset(string $string): void
    {
        $this->swiftMessage->setCharset($string);
    }

    public function setPriority(int $int): void
    {
        $this->swiftMessage->setPriority($int);
    }

    public function setFrom(string $email, string $name = ''): void
    {
        $this->swiftMessage->setFrom($email, $name);
    }

    public function setBounceAddress(string $email): void
    {
        $this->swiftMessage->setReturnPath($email);
    }

    public function setSubject(string $subject): void
    {
        $this->swiftMessage->setSubject($subject);
    }

    public function setBody(string $string, $mime = 'text/html'): void
    {
        $this->swiftMessage->setBody($string);
        $this->swiftMessage->setContentType($mime);
    }

    public function setAlternativeBody(string $string): void
    {
        $this->swiftMessage->addPart($string, 'plain/text');
    }

    public function addReplyToAddresses(Message $message): void
    {
        $recipients = [];
        foreach ($message->getReplyTo() as $recipient) {
            $recipients[] = $recipient[0];
        }
        $this->swiftMessage->setReplyTo($recipients);
    }

    public function addTo(string $email, string $name = ''): void
    {
        $this->swiftMessage->addTo($email, $name);
    }

    public function addCCs(Message $message): void
    {
        foreach ($message->getCc() as $recipient) {
            $this->swiftMessage->addCc(...$recipient);
        }
    }

    public function addBCCs(Message $message): void
    {
        foreach ($message->getBcc() as $recipient) {
            $this->swiftMessage->addBcc(...$recipient);
        }
    }

    public function addDynamicAttachments(Message $message): void
    {
        foreach ($message->getDynamicAttachments() as $attachment) {
            $swiftAttachment = new \Swift_Attachment(...$attachment);
            $this->swiftMessage->attach($swiftAttachment);
        }
    }

    public function addFileAttachments(Message $message): void
    {
        foreach ($message->getFileAttachments() as $attachment) {
            $swiftAttachment = new \Swift_Attachment(...$attachment);
            $this->swiftMessage->attach($swiftAttachment);
        }
    }

    public function addDynamicEmbeds(Message $message): void
    {
        foreach ($message->getDynamicEmbeds() as $embed) {
            $swiftAttachment = new \Swift_Attachment($embed[0], $embed[2], $embed[3]);
            $swiftAttachment = $swiftAttachment
                ->setDisposition('inline')
                ->setId($embed[1]);
            $this->swiftMessage->attach($swiftAttachment);
        }
    }

    public function addFileEmbeds(Message $message): void
    {
        foreach ($message->getFileEmbeds() as $embed) {
            $swiftAttachment = new \Swift_Attachment($embed[0], $embed[2], $embed[3]);
            $swiftAttachment = $swiftAttachment
                ->setDisposition('inline')
                ->setId($embed[1]);
            $this->swiftMessage->attach($swiftAttachment);
        }
    }
}
