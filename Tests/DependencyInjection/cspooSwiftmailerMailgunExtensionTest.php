<?php

namespace cspoo\Swiftmailer\MailgunBundle\Tests\DependencyInjection;

use cspoo\Swiftmailer\MailgunBundle\DependencyInjection\cspooSwiftmailerMailgunExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class cspooSwiftmailerMailgunExtensionTest extends AbstractExtensionTestCase
{
    protected function getContainerExtensions()
    {
        return array(
            new cspooSwiftmailerMailgunExtension()
        );
    }

    /**
     * @inheritDoc
     */
    protected function getMinimalConfiguration()
    {
        return array('key'=>'foo','domain'=>'bar');
    }


    /**
     * @test
     */
    public function after_loading_the_correct_parameter_has_been_set()
    {
        $this->load();

        $this->assertContainerBuilderHasParameter('mailgun.key', 'foo');
        $this->assertContainerBuilderHasParameter('mailgun.domain', 'bar');

        $this->assertContainerBuilderHasAlias('mailgun', 'mailgun.swift_transport.transport');
        $this->assertContainerBuilderHasAlias('swiftmailer.mailer.transport.mailgun', 'mailgun.swift_transport.transport');

        $this->assertContainerBuilderHasServiceDefinitionWithArgument('mailgun.swift_transport.transport', 0, 'mailgun.swift_transport.eventdispatcher');
    }
}