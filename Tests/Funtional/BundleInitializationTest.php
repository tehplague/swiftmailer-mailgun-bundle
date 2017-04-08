<?php

namespace Tests\Funtional;

use cspoo\Swiftmailer\MailgunBundle\cspooSwiftmailerMailgunBundle;
use cspoo\Swiftmailer\MailgunBundle\Service\MailgunTransport;
use Nyholm\BundleTest\BaseBundleTestCase;

/**
 * Make sure we can install the bundle on different versions of Symfony.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class BundleInitializationTest extends BaseBundleTestCase
{
    protected function getBundleClass()
    {
        return cspooSwiftmailerMailgunBundle::class;
    }

    public function testInitBundle()
    {
        // Boot the kernel.
        $this->bootKernel();

        // Get the container
        $container = $this->getContainer();

        // Test if you services exists
        $this->assertTrue($container->has('mailgun.swift_transport.transport'));
        $service = $container->get('mailgun.swift_transport.transport');
        $this->assertInstanceOf(MailgunTransport::class, $service);
    }
}