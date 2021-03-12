<?php

declare(strict_types=1);

namespace Djvue\DMediaBundle;

use Djvue\DMediaBundle\DependencyInjection\DMediaPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DMediaBundle extends Bundle
{

    public function boot(): void
    {
        /*
         * $loader = $this->container->get(LoaderInterface::class);
        if ($loader === null) {
            throw new AutowiringFailedException('Dependency implements LoaderInterface not found');
        }
        self::$loader = $loader;

        $loader->loadCore();
        */
    }

    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new DMediaPass());
    }
}
