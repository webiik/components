<?php
declare(strict_types=1);

namespace Webiik\Mail\Mailer;

use Webiik\Container\Container;
use Webiik\Log\Log;
use Webiik\Mail\Message;

class SwiftMailer implements MailerInterface
{
    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var Container
     */
    private $container;

    /**
     * @param \Swift_Mailer $mailer
     * @param Container $container
     */
    public function __construct(\Swift_Mailer $mailer, Container $container)
    {
        $this->mailer = $mailer;
        $this->container = $container;
    }

    /**
     * Send all messages
     * @param array $messages
     * @return mixed|void
     */
    public function send(array $messages)
    {
        foreach ($messages as $message) {
            $swiftMessage = new \Swift_Message();
            /** @var Message $message */
            $swiftMessage = $this->setCharset($message, $swiftMessage);
            $swiftMessage = $this->setPriority($message, $swiftMessage);
            $swiftMessage = $this->setFrom($message, $swiftMessage);
            $swiftMessage = $this->setReplyTo($message, $swiftMessage);
            $swiftMessage = $this->setBounceAddress($message, $swiftMessage);
            $swiftMessage = $this->addTo($message, $swiftMessage);
            $swiftMessage = $this->addCc($message, $swiftMessage);
            $swiftMessage = $this->addBcc($message, $swiftMessage);
            $swiftMessage = $this->setSubject($message, $swiftMessage);
            $swiftMessage = $this->setBody($message, $swiftMessage);
            $swiftMessage = $this->setAlternativeBody($message, $swiftMessage);
            $swiftMessage = $this->addDynamicAttachment($message, $swiftMessage);
            $swiftMessage = $this->addFileAttachment($message, $swiftMessage);
            $swiftMessage = $this->addDynamicEmbed($message, $swiftMessage);
            $swiftMessage = $this->addFileEmbed($message, $swiftMessage);
            $failedRecipients = [];
            $this->mailer->send($swiftMessage, $failedRecipients);
            if ($failedRecipients && $this->container->isIn('Webiik\Log\Log')) {
                foreach ($failedRecipients as $failedRecipient) {
                    $this->logError($failedRecipient, $message->getSubject());
                }
            }
        }
    }

    /**
     * @return \Swift_Mailer
     */
    public function core(): \Swift_Mailer
    {
        return $this->mailer;
    }

    /**
     * @param Message $message
     * @param \Swift_Message $swiftMessage
     * @return \Swift_Message
     */
    private function setBody(Message $message, \Swift_Message $swiftMessage): \Swift_Message
    {
        $body = $message->getBody();
        if ($body) {
            $swiftMessage = $swiftMessage->setBody($body[0]);
            $swiftMessage = $swiftMessage->setContentType($body[1]);
        }
        return $swiftMessage;
    }

    /**
     * @param Message $message
     * @param \Swift_Message $swiftMessage
     * @return \Swift_Message
     */
    private function setAlternativeBody(Message $message, \Swift_Message $swiftMessage): \Swift_Message
    {
        $body = $message->getAlternativeBody();
        if ($body) {
            $swiftMessage = $swiftMessage->addPart($body, 'plain/text');
        }
        return $swiftMessage;
    }

    /**
     * @param Message $message
     * @param \Swift_Message $swiftMessage
     * @return \Swift_Message
     */
    private function setCharset(Message $message, \Swift_Message $swiftMessage): \Swift_Message
    {
        return $swiftMessage->setCharset($message->getCharset());
    }

    /**
     * @param Message $message
     * @param \Swift_Message $swiftMessage
     * @return \Swift_Message
     */
    private function setPriority(Message $message, \Swift_Message $swiftMessage): \Swift_Message
    {
        $priority = $message->getPriority();
        if ($priority) {
            $swiftMessage = $swiftMessage->setPriority($priority);
        }
        return $swiftMessage;
    }

    /**
     * Set from email and name
     * @param Message $message
     * @param \Swift_Message $swiftMessage
     * @return \Swift_Message
     */
    private function setFrom(Message $message, \Swift_Message $swiftMessage): \Swift_Message
    {
        return $swiftMessage->setFrom(...$message->getFrom());
    }

    /**
     * @param Message $message
     * @param \Swift_Message $swiftMessage
     * @return \Swift_Message
     */
    private function setReplyTo(Message $message, \Swift_Message $swiftMessage): \Swift_Message
    {
        $recipients = [];
        foreach ($message->getReplyTo() as $recipient) {
            $recipients[] = $recipient[0];
        }
        return $swiftMessage->setReplyTo($recipients);
    }

    /**
     * @param Message $message
     * @param \Swift_Message $swiftMessage
     * @return \Swift_Message
     */
    private function setBounceAddress(Message $message, \Swift_Message $swiftMessage): \Swift_Message
    {
        return $swiftMessage->setReturnPath($message->getBounceAddress());
    }

    /**
     * @param Message $message
     * @param \Swift_Message $swiftMessage
     * @return \Swift_Message
     */
    private function setSubject(Message $message, \Swift_Message $swiftMessage): \Swift_Message
    {
        return $swiftMessage->setSubject($message->getSubject());
    }

    /**
     * Add recipients
     * @param Message $message
     * @param \Swift_Message $swiftMessage
     * @return \Swift_Message
     */
    private function addTo(Message $message, \Swift_Message $swiftMessage): \Swift_Message
    {
        foreach ($message->getTo() as $recipient) {
            $swiftMessage = $swiftMessage->addTo(...$recipient);
        }
        return $swiftMessage;
    }

    /**
     * @param Message $message
     * @param \Swift_Message $swiftMessage
     * @return \Swift_Message
     */
    private function addCc(Message $message, \Swift_Message $swiftMessage): \Swift_Message
    {
        foreach ($message->getCc() as $recipient) {
            $swiftMessage = $swiftMessage->addCc(...$recipient);
        }
        return $swiftMessage;
    }

    /**
     * @param Message $message
     * @param \Swift_Message $swiftMessage
     * @return \Swift_Message
     */
    private function addBcc(Message $message, \Swift_Message $swiftMessage): \Swift_Message
    {
        foreach ($message->getBcc() as $recipient) {
            $swiftMessage = $swiftMessage->addBcc(...$recipient);
        }
        return $swiftMessage;
    }

    /**
     * Attach dynamic content to message
     * @param Message $message
     * @param \Swift_Message $swiftMessage
     * @return \Swift_Message
     */
    private function addDynamicAttachment(Message $message, \Swift_Message $swiftMessage): \Swift_Message
    {
        foreach ($message->getDynamicAttachments() as $attachment) {
            $swiftAttachment = new \Swift_Attachment($attachment[0], $attachment[1], $attachment[2]);
            $swiftMessage = $swiftMessage->attach($swiftAttachment);
        }

        return $swiftMessage;
    }

    /**
     * Attach files to message
     * @param Message $message
     * @param \Swift_Message $swiftMessage
     * @return \Swift_Message
     */
    private function addFileAttachment(Message $message, \Swift_Message $swiftMessage): \Swift_Message
    {
        foreach ($message->getFileAttachments() as $attachment) {
            $swiftAttachment = new \Swift_Attachment($attachment[0], $attachment[1], $attachment[2]);
            $swiftMessage = $swiftMessage->attach($swiftAttachment);
        }

        return $swiftMessage;
    }

    /**
     * Embed dynamic content to message (usually images)
     * @param Message $message
     * @param \Swift_Message $swiftMessage
     * @return \Swift_Message
     */
    private function addDynamicEmbed(Message $message, \Swift_Message $swiftMessage): \Swift_Message
    {
        foreach ($message->getDynamicEmbeds() as $embed) {
            $swiftAttachment = new \Swift_Attachment($embed[0], $embed[2], $embed[3]);
            $swiftAttachment = $swiftAttachment
                ->setDisposition('inline')
                ->setId($embed[1]);
            $swiftMessage = $swiftMessage->attach($swiftAttachment->setDisposition('inline'));
        }

        return $swiftMessage;
    }

    /**
     * Embed files to message (usually images)
     * @param Message $message
     * @param \Swift_Message $swiftMessage
     * @return \Swift_Message
     */
    private function addFileEmbed(Message $message, \Swift_Message $swiftMessage): \Swift_Message
    {
        foreach ($message->getFileEmbeds() as $embed) {
            $swiftAttachment = new \Swift_Attachment($embed[0], $embed[2], $embed[3]);
            $swiftAttachment = $swiftAttachment
                ->setDisposition('inline')
                ->setId($embed[1]);
            $swiftMessage = $swiftMessage->attach($swiftAttachment->setDisposition('inline'));
        }

        return $swiftMessage;
    }

    /**
     * @param string $email
     * @param string $subject
     */
    private function logError(string $email, string $subject): void
    {
        /** @var Log $log */
        $log = $this->container->get('Webiik\Log\Log');
        $logMsg = 'Unable to send "{subject}" to: {email}';
        $log->log($log::NOTICE, $logMsg, [
            'subject' => $subject,
            'email' => $email,
        ]);
    }
}
