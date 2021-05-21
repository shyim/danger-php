<?php
declare(strict_types=1);

namespace Danger;

use Danger\DependencyInjection\Container;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Application extends SymfonyApplication
{
    private ContainerInterface $container;

    public function __construct()
    {
        parent::__construct('Danger', '__VERSION__');
        $this->container = Container::getContainer();

        foreach (array_keys($this->container->findTaggedServiceIds('console.command')) as $command) {
            /** @var Command $command */
            $command = $this->container->get($command);

            $this->add($command);
        }
    }

    public function getContainer(): ContainerBuilder
    {
        return $this->container;
    }
}
