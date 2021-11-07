<?php

declare(strict_types=1);

namespace Djvue\DMediaBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * @internal
 * @group init
 */
class DMediaExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('d_media.storage.public_url', $config['storage']['public_url']);
        $container->setParameter('d_media.storage.directory', $config['storage']['directory']);
        $container->setParameter('d_media.library.image_extensions', $config['library']['image_extensions']);
        $container->setParameter('d_media.filterable_entities', $config['filterable_entities']);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');
        if ($container->getParameter('kernel.environment') === 'test') {
            $loader->load('services_test.yaml');
        }
    }
}
