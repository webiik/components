<?php
declare(strict_types=1);

namespace Webiik\Mail;

use Webiik\Container\Container;

class Mail
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var string
     */
    private $mailer;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

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
     * @return mixed
     */
    public function send(array $messages)
    {
        return $this->container->get($this->mailer)->send($messages);
    }

    /**
     * Set mailer service from Container eg. PHPMailer
     * Note: Mailer service has to implement MailerInterface
     * @param string $mailer
     */
    public function setMailer(string $mailer):void
    {
        $this->mailer = $mailer;
    }

    /**
     * Get underlying mailer of mailer service
     * @return object
     */
    public function getMailerCore()
    {
        return $this->container->get($this->mailer)->core();
    }
}
