<?php
declare(strict_types=1);

namespace Webiik\Mail;

class Mail
{
    /**
     * @var callable
     */
    private $mailer;

    /**
     * Create message which can be send
     * Imagine it as inscribed envelope with content inside
     * @return Message
     */
    public function createMessage(): Message
    {
        return new Message();
    }

    /**
     * Send messages
     * @param array $messages
     * @return array Array of undelivered addresses.
     */
    public function send(array $messages): array
    {
        $mailer = $this->mailer;
        return $mailer()->send($messages);
    }

    /**
     * Set mailer service factory
     * Note: Mailer service has to implement MailerInterface
     * @param callable $factory
     */
    public function setMailer(callable $factory):void
    {
        $this->mailer = $factory;
    }

    /**
     * Get underlying mailer of mailer service
     * @return object
     */
    public function getMailerCore()
    {
        $mailer = $this->mailer;
        return $mailer()->core();
    }
}
