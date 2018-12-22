<?php
declare(strict_types=1);

namespace Webiik\Mail\Mailer;

interface MailerInterface
{
    /**
     * Send all Messages in $messages array
     * @param array $messages
     * @return mixed
     */
    public function send(array $messages);

    /**
     * Return underlying mailer
     * @return object
     */
    public function core();
}
