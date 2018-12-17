

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
composer require cspoo/swiftmailer-mailgun-bundle php-http/guzzle5-adapter
```

*Note: You can use any of [these adapters](https://packagist.org/providers/php-http/client-implementation)*

## Configuration

### Symfony 3.4

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
    endpoint: "https://api.eu.mailgun.net" # Optional. Use this config for EU region. Defaults to "https://api.mailgun.net"
    http_client: "httplug.client" # Optional. Defaults to null and uses discovery to find client. 

# Swiftmailer Configuration
swiftmailer:
    transport: "mailgun"
    spool:     { type: memory } # This will start sending emails on kernel.terminate event

```
Note that the swiftmailer configuration is the same as the standard one - you just 
change the mailer_transport parameter.

### Symfony 4.1

Add your Mailgun credentials 
```bash
# both .env and .env.dist files
MAILGUN_DOMAIN=<your domain>
MAILGUN_API_KEY=<your key>
MAILGUN_SENDER=<your sender>
```

Adding to you bundle
```php
// config/bundles.php
return [
    ...
    cspoo\Swiftmailer\MailgunBundle\cspooSwiftmailerMailgunBundle::class => ['all' => true],
    ];
```

Configure your Mailgun credentials:

```yaml
// config/packages/mailgun.yaml
cspoo_swiftmailer_mailgun:
    key: '%env(MAILGUN_API_KEY)%'
    domain: "%env(MAILGUN_DOMAIN)%"

services:
    Mailgun\Mailgun:
        class: Mailgun\Mailgun
        factory: ['Mailgun\Mailgun', create]
        arguments: ['%env(MAILGUN_API_KEY)%']
```

Finally, add the following line on swiftmailer config:
```yaml
// config/packages/swiftmailer.yaml
swiftmailer:
    # url: '%env(MAILER_URL)%'
    transport: 'mailgun'
    spool: { type: 'memory' }
```
Note: Not sure if url line should be commented.

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

You can also test through terminal using:
```bash
bin/console swiftmailer:email:send --from=<from email> --to=<to email> --subject="Foo" --body="Bar"
```

## Choose HTTP client

Mailgun 2.0 is no longer coupled to Guzzle5. Thanks to [Httplug](http://docs.php-http.org/en/latest/index.html) you can now use any
library to transport HTTP messages. You can rely on [discovery](http://docs.php-http.org/en/latest/discovery.html) to automatically
find an installed client or you can use [HttplugBundle](https://github.com/php-http/HttplugBundle) and provide a client service name 
to the mailgun configuration. 

``` yaml
// app/config/config.yml:
cspoo_swiftmailer_mailgun:
    http_client: 'httplug.client'
```


