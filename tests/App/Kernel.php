<?php

declare(strict_types=1);

namespace Djvue\DMediaBundle\Tests\App;

use Djvue\DMediaBundle\DMediaBundle;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle;
use Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle;
use League\FlysystemBundle\FlysystemBundle;
use Liip\TestFixturesBundle\LiipTestFixturesBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function __construct()
    {
        parent::__construct('test', true);
    }

    public function registerBundles()
    {
        return [
            new FrameworkBundle(),
            new DoctrineBundle(),
            new DoctrineMigrationsBundle(),
            new FlysystemBundle(),
            new DoctrineFixturesBundle(),
            new LiipTestFixturesBundle(),
            new DMediaBundle(),
        ];
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->parameters()->set('app.base_dir', $this->getBaseDir());
        $container->import(__DIR__.'/config.yaml');
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import('routes.yaml');
    }

    private function getBaseDir(): string
    {
        return sys_get_temp_dir().'/d-media-bundle/var/';
    }
}
