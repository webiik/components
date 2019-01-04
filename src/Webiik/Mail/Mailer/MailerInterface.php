<?php
declare(strict_types=1);

namespace Webiik\Mail\Mailer;

use Webiik\Mail\Message;

interface MailerInterface
{
    /**
     * Return object of underlying mailer
     * @return mixed
     */
    public function core();

    /**
     * Send all Messages in $messages array
     * @param array $messages Array of Message objects
     * @return array Array of undelivered email addresses
     */
    public function send(array $messages): array;

    /**
     * Implement Message method to work with created mailer
     * @param string $charset
     */
    public function setCharset(string $charset): void;

    /**
     * Implement Message method to work with created mailer
     * @param int $int
     */
    public function setPriority(int $int): void;

    /**
     * Implement Message method to work with created mailer
     * @param string $email
     * @param string $name
     */
    public function setFrom(string $email, string $name = ''): void;

    /**
     * Implement Message method to work with created mailer
     * @param string $email
     */
    public function setBounceAddress(string $email): void;

    /**
     * Implement Message method to work with created mailer
     * @param string $subject
     */
    public function setSubject(string $subject): void;

    /**
     * Implement Message method to work with created mailer
     * @param string $string
     * @param string $mime - usually text/html or text/plain
     */
    public function setBody(string $string, $mime = 'text/html'): void;

    /**
     * Implement Message method to work with created mailer
     * @param string $string
     */
    public function setAlternativeBody(string $string): void;

    /**
     * Implement Message method addReplyTo to work with created mailer
     * This implementation MUST add all addresses added with addReplyTo method of Message class
     * @param Message $message
     */
    public function addReplyToAddresses(Message $message): void;

    /**
     * Implement Message method to work with created mailer
     * @param string $email
     * @param string $name
     */
    public function addTo(string $email, string $name = ''): void;

    /**
     * Implement Message method addCc to work with created mailer
     * This implementation MUST add all addresses added with addCc method of Message class
     * @param Message $message
     */
    public function addCCs(Message $message): void;

    /**
     * Implement Message method addBcc to work with created mailer
     * This implementation MUST add all addresses added with addBcc method of Message class
     * @param Message $message
     */
    public function addBCCs(Message $message): void;

    /**
     * Implement Message method addDynamicAttachment to work with created mailer
     * This implementation MUST add all attachments added with addDynamicAttachment method of Message class
     * @param Message $message
     */
    public function addDynamicAttachments(Message $message): void;

    /**
     * Implement Message method addFileAttachment to work with created mailer
     * This implementation MUST add all attachments added with addFileAttachment method of Message class
     * @param Message $message
     */
    public function addFileAttachments(Message $message): void;

    /**
     * Implement Message method addDynamicEmbed to work with created mailer
     * This implementation MUST add all attachments added with addDynamicEmbed method of Message class
     * @param Message $message
     */
    public function addDynamicEmbeds(Message $message): void;

    /**
     * Implement Message method addFileEmbed to work with created mailer
     * This implementation MUST add all attachments added with addFileEmbed method of Message class
     * @param Message $message
     */
    public function addFileEmbeds(Message $message): void;
}
