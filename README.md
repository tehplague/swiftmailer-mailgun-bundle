
# Swiftmailer Mailgun bundle

[![Latest Stable Version](https://poser.pugx.org/cspoo/swiftmailer-mailgun-bundle/v/stable)](https://packagist.org/packages/cspoo/swiftmailer-mailgun-bundle)
[![codecov.io](https://codecov.io/github/tehplague/swiftmailer-mailgun-bundle/coverage.svg?branch=master)](https://codecov.io/github/tehplague/swiftmailer-mailgun-bundle?branch=master)
[![Total Downloads](https://poser.pugx.org/cspoo/swiftmailer-mailgun-bundle/downloads)](https://packagist.org/packages/cspoo/swiftmailer-mailgun-bundle)
[![Monthly Downloads](https://poser.pugx.org/cspoo/swiftmailer-mailgun-bundle/d/monthly.png)](https://packagist.org/packages/cspoo/swiftmailer-mailgun-bundle)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)


This bundle adds an extra transport to the swiftmailer service that uses the mailgun
http interface for sending messages.

## Installation

```bash
composer require cspoo/swiftmailer-mailgun-bundle
```

Also add to your AppKernel:

```php
new cspoo\Swiftmailer\MailgunBundle\cspooSwiftmailerMailgunBundle(),
```

Configure your application with the credentials you find on the [domain overview](https://mailgun.com/app/domains) on the Mailgun.com dashboard.

``` yaml
// app/config/config.yml:
cspoo_swiftmailer_mailgun:
    key: "key-xxxxxxxxxx"
    domain: "mydomain.com"

# Swiftmailer Configuration
swiftmailer:
    transport: "mailgun"
    spool:     { type: memory } # This will start sending emails on kernel.terminate event

```
Note that the swiftmailer configuration is the same as the standard one - you just 
change the mailer_transport parameter.

## Usage

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

Then send it as you normally would with the `mailer` service. Your configuration ensures that you will be using the Mailgun transport.

```php
$this->container->get('mailer')->send($message);
```

Todo:
 * [x] Add mailgun as a separate transport to the normal Swiftmailer service
 * [ ] Tests


