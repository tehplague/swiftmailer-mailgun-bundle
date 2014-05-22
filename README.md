
# Swiftmailer Mailgun bundle

This bundle adds an extra transport to the swiftmailer service that uses the mailgun
http interface for sending messages.

## Installation

```
composer require "cspoo/swiftmailer-mailgun-bundle"=dev-master
```

Also add to your AppKernel:

```
new cspoo\Swiftmailer\MailgunBundle\cspooSwiftmailerMailgunBundle();

```


## Usage:

First craft a message:

```php
$message = \Swift_Message::newInstance()
        ->setSubject('Hello Email')
        ->setFrom('send@example.com')
        ->setTo('recipient@example.com')
        ->setBody(
            $this->renderView(
                'HelloBundle:Hello:email.txt.twig',
                array('name' => $name)
            )
        )
    ;
```

Then use the mailgun service to send it:

```php
$mailgun = $this->container->get("mailgun.swift_transport.transport");

$mailgun->send($message);
```

Todo:
 * [ ] Add mailgun as a separate transport to the normal Swiftmailer service
 * [ ] Tests


