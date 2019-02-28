<?php
declare(strict_types=1);

namespace Webiik\Mail;

class Message
{
    /**
     * @var array
     */
    private $from = [];

    /**
     * @var string
     */
    private $subject = '';

    /**
     * @var array
     */
    private $to = [];

    /**
     * @var array
     */
    private $cc = [];

    /**
     * @var array
     */
    private $bcc = [];

    /**
     * @var array
     */
    private $replyTo = [];

    /**
     * @var string
     */
    private $bounceAddress = '';

    /**
     * @var array
     */
    private $body = [];

    /**
     * @var string
     */
    private $alternativeBody = '';

    /**
     * @var string
     */
    private $charset = 'utf-8';

    /**
     * @var int
     */
    private $priority = 3;

    /**
     * @var array
     */
    private $dynamicAttachments = [];

    /**
     * @var array
     */
    private $fileAttachments = [];

    /**
     * @var array
     */
    private $dynamicEmbeds = [];

    /**
     * @var array
     */
    private $fileEmbeds = [];

    /**
     * @param string $charset
     */
    public function setCharset(string $charset): void
    {
        $this->charset = $charset;
    }

    /**
     * Set message priority 1-5 (highest-lowest), 3 = normal
     * @param int $int
     */
    public function setPriority(int $int): void
    {
        $this->priority = $int;
    }

    /**
     * @param string $email
     * @param string $name
     */
    public function setFrom(string $email, string $name = ''): void
    {
        $this->from = [$email, $name];
    }

    /**
     * Set return address of undeliverable message
     * @param string $email
     */
    public function setBounceAddress(string $email): void
    {
        $this->bounceAddress = $email;
    }

    /**
     * @param string $subject
     */
    public function setSubject(string $subject): void
    {
        $this->subject = $subject;
    }

    /**
     * Set message body
     * @param string $string
     * @param string $mime - usually text/html or text/plain
     */
    public function setBody(string $string, $mime = 'text/html'): void
    {
        $this->body = [$string, $mime];
    }

    /**
     * Set alternative message body
     * Alternative body MUST be in text/plain
     * @param string $string
     */
    public function setAlternativeBody(string $string): void
    {
        $this->alternativeBody = $string;
    }

    /**
     * Add recipient
     * @param string $email
     * @param string $name
     */
    public function addTo(string $email, string $name = ''): void
    {
        $this->to[] = [$email, $name];
    }

    /**
     * @param string $email
     * @param string $name
     */
    public function addReplyTo(string $email, string $name = ''): void
    {
        $this->replyTo[] = [$email, $name];
    }

    /**
     * @param string $email
     * @param string $name
     */
    public function addCc(string $email, string $name = ''): void
    {
        $this->cc[] = [$email, $name];
    }

    /**
     * @param string $email
     * @param string $name
     */
    public function addBcc(string $email, string $name = ''): void
    {
        $this->bcc[] = [$email, $name];
    }

    /**
     * Add on the fly generated content as attachment
     * @param string $string
     * @param string $filename
     * @param string $mime
     */
    public function addDynamicAttachment(
        string $string,
        string $filename,
        string $mime = ''
    ): void {
        $this->dynamicAttachments[] = [$string, $filename, $mime];
    }

    /**
     * @param string $path
     * @param string $filename
     * @param string $mime
     */
    public function addFileAttachment(
        string $path,
        string $filename = '',
        string $mime = ''
    ): void {
        $this->fileAttachments[] = [$path, $filename, $mime];
    }

    /**
     * Embed on the fly generated content to body of message
     * @param string $string
     * @param string $cid
     * @param string $filename
     * @param string $mime
     */
    public function addDynamicEmbed(
        string $string,
        string $cid,
        string $filename = '',
        string $mime = ''
    ): void {
        $this->dynamicEmbeds[] = [$string, $cid, $filename, $mime];
    }

    /**
     * Embed file to body of message (usually image)
     * @param string $path
     * @param string $cid
     * @param string $filename
     * @param string $mime
     */
    public function addFileEmbed(
        string $path,
        string $cid,
        string $filename = '',
        string $mime = ''
    ): void {
        $this->fileEmbeds[] = [$path, $cid, $filename, $mime];
    }

    /**
     * @return string
     */
    public function getCharset(): string
    {
        return $this->charset;
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * @return array
     */
    public function getFrom(): array
    {
        return $this->from;
    }

    /**
     * @return string
     */
    public function getBounceAddress(): string
    {
        return $this->bounceAddress;
    }

    /**
     * @return array
     */
    public function getTo(): array
    {
        return $this->to;
    }

    /**
     * @return array
     */
    public function getReplyTo(): array
    {
        return $this->replyTo;
    }

    /**
     * @return array
     */
    public function getCc(): array
    {
        return $this->cc;
    }

    /**
     * @return array
     */
    public function getBcc(): array
    {
        return $this->bcc;
    }

    /**
     * @return string
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * @return array
     */
    public function getBody(): array
    {
        return $this->body;
    }

    /**
     * @return string
     */
    public function getAlternativeBody(): string
    {
        return $this->alternativeBody;
    }

    /**
     * @return array
     */
    public function getDynamicAttachments(): array
    {
        return $this->dynamicAttachments;
    }

    /**
     * @return array
     */
    public function getFileAttachments(): array
    {
        return $this->fileAttachments;
    }

    /**
     * @return array
     */
    public function getDynamicEmbeds(): array
    {
        return $this->dynamicEmbeds;
    }

    /**
     * @return array
     */
    public function getFileEmbeds(): array
    {
        return $this->fileEmbeds;
    }
}
