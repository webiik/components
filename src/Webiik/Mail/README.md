Mail
====
The Mail brings common interface for sending emails, no matter what mail library you want to use. Out of the box it supports PHPMailer and SwiftMailer.

Example
-------
```php
$mail = new \Webiik\Mail\Mail();

// Add PHPMailer
$mail->setMailer(function () {
    return new \Webiik\Mail\Mailer\PHPMailer(new \PHPMailer\PHPMailer\PHPMailer());
});

// Create Message
$message = $mail->createMessage();

// Configure charset and priority
$message->setCharset('utf-8');
$message->setPriority(3);

// Configure sender, bounce address and recipients
$message->setFrom('your@email.tld', 'Firstname Lastname');
$message->setBounceAddress('your@email.tld');
$message->addTo('your@email.tld', 'Firstname Lastname');
$message->addReplyTo('your@email.tld');
$message->addCc('your@email.tld');
$message->addBcc('your@email.tld');

// Configure message
$message->setSubject('Test subject');
$message->setBody('Hello world, this is body of test message.');
$message->setAlternativeBody('Hellow world, this is alternative body of test message.');
$message->addFileAttachment(__DIR__ . '/nice_picture.jpg');

// Send Message
$unsent = $mail->send([$message]);

// Get unsent email addresses
foreach ($unsent as $email) {
    // Do something with undelivered email
}
```

Mailers
-------
#### Add mailer
To send messages you have to add mailer to Mail class. Out of the box you can choose from PHPMailer or SwiftMailer.
```php
setMailer(callable $factory):void
```
```php
$mail->setMailer(function () {
    return new \Webiik\Mail\Mailer\PHPMailer(new \PHPMailer\PHPMailer\PHPMailer());
});
```
#### Access mailer core library
If you need it, you can access underlying library of mailer.
```php
getMailerCore()
```
```php
$mail->getMailerCore(); // Return eg. \PHPMailer\PHPMailer\PHPMailer()
```
#### Create Custom Mailer
To write your custom mailer, your have to implement interface `MailerInterface`.
```php
// CustomMailer.php
declare(strict_types=1);

namespace Webiik\Mail\Mailer;

use Webiik\Mail\Message;

class CustomMailer implements MailerInterface
{
    // Your implementation...
```

Message
-------
#### Create
```php
createMessage(): Message
```
```php
$message = $mail->createMessage();
```
#### Miscellaneous
```php
setCharset(string $charset): void
```
```php
getCharset(): string
```
```php
setPriority(int $int): void
```
```php
getPriority(): int
```
> Priority 1-5 (highest-lowest), 3 = normal
#### Sender
```php
setFrom(string $email, string $name = ''): void
```
```php
getFrom(): array
```
```php
setBounceAddress(string $email): void
```
```php
getBounceAddress(): string
```
#### Recipients
```php
addTo(string $email, string $name = ''): void
```
```php
getTo(): array
```
```php
addReplyTo(string $email, string $name = ''): void
```
```php
getReplyTo(): array
```
```php
addCc(string $email, string $name = ''): void
```
```php
getCc(): array
```
```php
addBcc(string $email, string $name = ''): void
```
```php
getBcc(): array
```
#### Subject
```php
setSubject(string $subject): void
```
```php
getSubject(): string
```
#### Body
```php
setBody(string $string, $mime = 'text/html'): void
```
```php
getBody(): array
```
```php
setAlternativeBody(string $string): void
```
```php
getAlternativeBody(): string
```
> Alternative body for clients without support of text/html.
#### Attachments
```php
addDynamicAttachment(string $string, string $filename, string $mime = ''): void
```
```php
getDynamicAttachments(): array
```
```php
addFileAttachment(string $path, string $filename = '', string $mime = ''): void
```
```php
getFileAttachments(): array
```
```php
addDynamicEmbed(string $string, string $cid, string $filename = '', string $mime = ''): void
```
```php
getDynamicEmbeds(): array
```
```php
addFileEmbed(string $path, string $cid, string $filename = '', string $mime = ''): void
```
```php
getFileEmbeds(): array
```
#### Send
```php
send(array $messages): array
```

Resources
---------
* [Webiik framework][1]
* [Report issues][2]

[1]: https://github.com/webiik/webiik
[2]: https://github.com/webiik/webiik-components/issues