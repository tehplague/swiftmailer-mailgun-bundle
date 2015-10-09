<?php

namespace cspoo\Swiftmailer\MailgunBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class cspooSwiftmailerMailgunExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('mailgun.key', $config['key']);
        $container->setParameter('mailgun.domain', $config['domain']);

        $definitionDecorator = new DefinitionDecorator('swiftmailer.transport.eventdispatcher.abstract');
        $container->setDefinition('mailgun.swift_transport.eventdispatcher', $definitionDecorator);

        $container->getDefinition('mailgun.swift_transport.transport')
            ->replaceArgument(0, new Reference('mailgun.swift_transport.eventdispatcher'));

        //set some alias
        $container->setAlias('mailgun', 'mailgun.swift_transport.transport');
        $container->setAlias('swiftmailer.mailer.transport.mailgun', 'mailgun.swift_transport.transport');
    }
}
