<p align="left">
<img src="https://img.shields.io/packagist/l/webiik/webiik.svg"/>
<img src="https://img.shields.io/badge/dependencies-0-brightgreen.svg"/>
</p>

Mail
====
The Mail brings common interface for sending emails, no matter what mail library you want to use. Out of the box it supports PHPMailer and SwiftMailer.

Installation
------------
```bash
composer require webiik/mail
```

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

// Set charset and priority of Message
$message->setCharset('utf-8');
$message->setPriority(3);

// Set Message sender, bounce address and recipients
$message->setFrom('your@email.tld', 'Firstname Lastname');
$message->setBounceAddress('your@email.tld');
$message->addTo('your@email.tld', 'Firstname Lastname');
$message->addReplyTo('your@email.tld');
$message->addCc('your@email.tld');
$message->addBcc('your@email.tld');

// Set Message subject and body
$message->setSubject('Test subject');
$message->setBody('Hello world, this is body of test message.');
$message->setAlternativeBody('Hellow world, this is alternative body of test message.');

// Set Message attachment
$message->addFileAttachment(__DIR__ . '/nice_picture.jpg');

// Send Message
$unsent = $mail->send([$message]);

// Get unsent email addresses
foreach ($unsent as $email) {
    // Do something with undelivered email
}
```

Mail
----
### setMailer
```php
setMailer(callable $factory):void
```
**setMailer()** sets mailer. Out of the box you can choose from PHPMailer or SwiftMailer.
```php
$mail->setMailer(function () {
    return new \Webiik\Mail\Mailer\PHPMailer(new \PHPMailer\PHPMailer\PHPMailer());
});
```
> Don't forget to install library used by mailer e.g. `composer require phpmailer/phpmailer`

**Write Custom Mailer**

You can write your custom mailer. To write your custom mailer, you have to implement interface `Webiik\Mail\Mailer\MailerInterface`.
```php
// CustomMailer.php
declare(strict_types=1);

namespace Webiik\Mail\Mailer;

use Webiik\Mail\Message;

class CustomMailer implements MailerInterface
{
    // Your implementation...
``` 

### getMailerCore
```php
getMailerCore()
```
**getMailerCore()** returns mailer core library e.g. \PHPMailer\PHPMailer\PHPMailer()
```php
$phpMailer = $mail->getMailerCore(); 
```

### send
```php
send(array $messages): array
```
**send()** sends array of [**Message**](#message) objects. Returns array of un-send email addresses. To send Messages you have to [set mailer](#setmailer) to Mail class.
```php
$mail->send($messages); 
```

Message
-------
### createMessage
```php
createMessage(): Message
```
**createMessage()** returns new **Message**.
```php
$message = $mail->createMessage();
```

**Overview Of Available Message Methods:**

### Miscellaneous
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

### Sender
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

### Recipients
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

### Subject
```php
setSubject(string $subject): void
```
```php
getSubject(): string
```

### Body
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

### Attachments
To attach existing file use:
```php
addFileAttachment(string $path, string $filename = '', string $mime = ''): void
```
```php
getFileAttachments(): array
```
To attach content generated on the fly use:
```php
addDynamicAttachment(string $string, string $filename, string $mime = ''): void
```
```php
getDynamicAttachments(): array
```

### Embeds
To embed existing image use:
```php
addFileEmbed(string $path, string $cid, string $filename = '', string $mime = ''): void
```
```php
getFileEmbeds(): array
```
To embed image generated on the fly use:
```php
addDynamicEmbed(string $string, string $cid, string $filename = '', string $mime = ''): void
```
```php
getDynamicEmbeds(): array
```

Resources
---------
* [Webiik framework][1]
* [Report issue][2]

[1]: https://github.com/webiik/webiik
[2]: https://github.com/webiik/components/issues