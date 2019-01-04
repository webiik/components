<?php
declare(strict_types=1);

namespace Webiik\Mail\Mailer;

use Webiik\Mail\Message;

class PHPMailer implements MailerInterface
{
    private $mailer;

    public function __construct(\PHPMailer\PHPMailer\PHPMailer $mailer)
    {
        $this->mailer = $mailer;
    }

    public function core(): \PHPMailer\PHPMailer\PHPMailer
    {
        return $this->mailer;
    }

    public function send(array $messages): array
    {
        $undelivered = [];

        foreach ($messages as $message) {
            /** @var Message $message */

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

                if (!$this->mailer->send()) {
                    $undelivered[] = $recipient[0];
                }

                $this->mailer->clearAddresses();
            }

            $this->mailer->clearAllRecipients();
            $this->mailer->clearAttachments();
        }

        return $undelivered;
    }

    public function setCharset(string $string): void
    {
        $this->mailer->CharSet = $string;
    }

    public function setPriority(int $int): void
    {
        $this->mailer->Priority = $int;
    }

    public function setFrom(string $email, string $name = ''): void
    {
        $this->mailer->setFrom($email, $name);
    }

    public function setBounceAddress(string $email): void
    {
        $this->mailer->Sender = $email;
    }

    public function setSubject(string $subject): void
    {
        $this->mailer->Subject = $subject;
    }

    public function setBody(string $string, $mime = 'text/html'): void
    {
        $this->mailer->Body = $string;
        $this->mailer->ContentType = $mime;
    }

    public function setAlternativeBody(string $string): void
    {
        $this->mailer->AltBody = $string;
    }

    public function addReplyToAddresses(Message $message): void
    {
        foreach ($message->getReplyTo() as $recipient) {
            $this->mailer->addReplyTo(...$recipient);
        }
    }

    public function addTo(string $email, string $name = ''): void
    {
        $this->mailer->addAddress($email, $name);
    }

    public function addCCs(Message $message): void
    {
        foreach ($message->getCc() as $recipient) {
            $this->mailer->addCC(...$recipient);
        }
    }

    public function addBCCs(Message $message): void
    {
        foreach ($message->getBcc() as $recipient) {
            $this->mailer->addBCC(...$recipient);
        }
    }

    public function addDynamicAttachments(Message $message): void
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

    public function addFileAttachments(Message $message): void
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

    public function addDynamicEmbeds(Message $message): void
    {
        foreach ($message->getDynamicEmbeds() as $attachment) {
            $this->mailer->addStringEmbeddedImage(
                $attachment[0],
                $attachment[1],
                $attachment[2],
                $this->mailer::ENCODING_BASE64,
                $attachment[3]
            );
        }
    }

    public function addFileEmbeds(Message $message): void
    {
        foreach ($message->getFileEmbeds() as $attachment) {
            $this->mailer->addEmbeddedImage(
                $attachment[0],
                $attachment[1],
                $attachment[2],
                $this->mailer::ENCODING_BASE64,
                $attachment[3]
            );
        }
    }
}
