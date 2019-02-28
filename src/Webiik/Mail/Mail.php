<?php
declare(strict_types=1);

namespace Webiik\Mail;

use Webiik\Mail\Mailer\MailerInterface;

class Mail
{
    /**
     * Mailer factory or instance after call getMailer
     * @var callable|MailerInterface
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
        return $this->getMailer()->send($messages);
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
        return $this->getMailer()->core();
    }

    /**
     * Get mailer instance
     * @return MailerInterface
     */
    private function getMailer(): MailerInterface
    {
        // Instantiate mailer only once
        if (is_callable($this->mailer)) {
            $mailer = $this->mailer;
            $this->mailer = $mailer();
        }

        return $this->mailer;
    }
}
