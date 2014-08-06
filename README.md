
# Swiftmailer Mailgun bundle

This bundle adds an extra transport to the swiftmailer service that uses the mailgun
http interface for sending messages.

## Installation

```
composer require "cspoo/swiftmailer-mailgun-bundle"=dev-master
```

Also add to your AppKernel:

```
new cspoo\Swiftmailer\MailgunBundle\cspooSwiftmailerMailgunBundle(),

```

Configure your bundle:

```app/config/config.yml:

parameters:
    mailgun_key: "Ppokpok"
    mailgun_domain: "mydomain.com"
    mailer_transport: mailgun


cspoo_swiftmailer_mailgun:
    key: "%mailgun_key%"
    domain: "%mailgun_domain%"


# Swiftmailer Configuration
swiftmailer:
    transport: "%mailer_transport%"
    host:      "%mailer_host%"
    username:  "%mailer_user%"
    password:  "%mailer_password%"
    spool:     { type: memory }

```

Note that the swiftmailer configuration is the same as the standard one - you just 
change the mailer_transport parameter.


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
 * [x] Add mailgun as a separate transport to the normal Swiftmailer service
 * [ ] Tests


