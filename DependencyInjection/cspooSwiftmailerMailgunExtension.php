<?php

namespace cspoo\Swiftmailer\MailgunBundle\DependencyInjection;

use Mailgun\HttpClientConfigurator;
use Mailgun\Mailgun;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Reference;
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

        $definitionDecorator = new ChildDefinition('swiftmailer.transport.eventdispatcher.abstract');
        $container->setDefinition('mailgun.swift_transport.eventdispatcher', $definitionDecorator);

        $container->getDefinition('mailgun.swift_transport.transport')
            ->replaceArgument(0, new Reference('mailgun.swift_transport.eventdispatcher'));

        $this->registerMailgunService($container, $config);

        //set some alias
        $container->setAlias('mailgun', 'mailgun.swift_transport.transport');
        $container->setAlias('swiftmailer.mailer.transport.mailgun', 'mailgun.swift_transport.transport');
    }

    /**
     * @param ContainerBuilder $container
     * @param array $config
     */
    private function registerMailgunService(ContainerBuilder $container, array $config)
    {
        $configuratorDef = new Definition(HttpClientConfigurator::class);
        $configuratorDef->addMethodCall('setApiKey', [$config['key']]);

        if (!empty($config['http_client'])) {
            $configuratorDef->addMethodCall('setHttpClient', [new Reference($config['http_client'])]);
        }

        $mailgunDef = new Definition(Mailgun::class);
        $mailgunDef->setFactory([Mailgun::class, 'configure'])
            ->addArgument($configuratorDef);

        $container->setDefinition('mailgun.library', $mailgunDef);
    }
}
