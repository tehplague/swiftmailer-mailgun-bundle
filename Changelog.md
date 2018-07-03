# Changelog

This document will tell you what changes that has been made between versions.

# Changes from 1.0.0 to 1.1.0

* Support for Symfony 4
* Dropped support for Symfony 2

# Changes from 0.4.0 to 1.0.0

* Support for Swift mailer 6. 

# Changes from 0.3.x to 0.4.0

* Removed the `mailgun.swift_transport.transport.class` parameter.
* Improved testing
* Avoid usage of deprecated Mailgun functions. 


# Changes from 0.2.x to 0.3.0

* Upgraded from Mailgun client 1.x to 2.x.
* Start using PSR-4
* Improved tests and documentation
* Changed namespace of `cspoo\Swiftmailer\MailgunBundle\Services\MailgunTransport` to `cspoo\Swiftmailer\MailgunBundle\Service\MailgunTransport`
* Catching all exception that may be thrown by the MailGun API.
* The return value of MaingunTransport::sendMessage is no longer fixed to 1. It does now comply with the interface.
