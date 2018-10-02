<?php

namespace cspoo\Swiftmailer\MailgunBundle\Tests\DependencyInjection;

use cspoo\Swiftmailer\MailgunBundle\DependencyInjection\cspooSwiftmailerMailgunExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Symfony\Component\DependencyInjection\Definition;

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
        return array('key' => 'foo', 'domain' => 'bar', 'endpoint' => 'baz');
    }


    /**
     * @test
     */
    public function after_loading_the_correct_parameter_has_been_set()
    {
        $this->load();

        $this->assertContainerBuilderHasParameter('mailgun.key', 'foo');
        $this->assertContainerBuilderHasParameter('mailgun.domain', 'bar');

        /** @var Definition $definition */
        $definition = $this->container->getDefinition('mailgun.library')->getArgument(0);
        foreach ($definition->getMethodCalls() as $methodCall) {
            if ($methodCall[0] !== 'setEndpoint') {
                continue;
            }

            $setEndpointArguments = $methodCall[1];
            $this->assertEquals('baz', $setEndpointArguments[0]);
            break;
        }

        $this->assertContainerBuilderHasAlias('mailgun', 'mailgun.swift_transport.transport');
        $this->assertContainerBuilderHasAlias('swiftmailer.mailer.transport.mailgun', 'mailgun.swift_transport.transport');

        $this->assertContainerBuilderHasServiceDefinitionWithArgument('mailgun.swift_transport.transport', 0, 'mailgun.swift_transport.eventdispatcher');
    }
}