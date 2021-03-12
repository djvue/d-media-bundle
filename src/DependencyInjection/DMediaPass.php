<?php

declare(strict_types=1);

namespace Djvue\DMediaBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @internal
 * @group init
 */
class DMediaPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        // $container->setAlias(LoaderInterface::class, Loader::class)->setPublic(true);
        // $container->autowire(Loader::class, Loader::class);
        // $container->setAlias(ConfigurationLoaderInterface::class, ConfigurationLoader::class)->setPublic(true);
        // $container->autowire(ConfigurationLoader::class);
    }
}
