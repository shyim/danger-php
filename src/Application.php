<?php
declare(strict_types=1);

namespace Danger;

use Danger\DependencyInjection\Container;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class Application extends SymfonyApplication
{
    public const PACKAGE_NAME = 'shyim/danger-php';
    public const RULE_DEPRECATION_MESSAGE = 'This rule is deprecated. Use %s instead.';

    private ContainerBuilder $container;

    public function __construct()
    {
        parent::__construct('Danger', '__VERSION__');
        $this->container = Container::getContainer();

        foreach (array_keys($this->container->findTaggedServiceIds('console.command')) as $taggedService) {
            /** @var Command $command */
            $command = $this->container->get($taggedService);

            $this->add($command);
        }
    }

    public function getContainer(): ContainerBuilder
    {
        return $this->container;
    }
}
