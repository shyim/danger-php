<?php
declare(strict_types=1);

namespace Danger\Command;

use Danger\ConfigLoader;
use Danger\Context;
use Danger\Platform\Github\Github;
use Danger\Runner;
use InvalidArgumentException;
use function is_string;
use RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GithubCommand extends AbstractPlatformCommand
{
    public static $defaultName = 'github-local';

    public function __construct(private Github $github, private ConfigLoader $configLoader, private Runner $runner)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Run local danger against an Github PR without Commenting')
            ->addArgument('pr', InputArgument::REQUIRED, 'Github PR URL')
            ->addOption('config', 'c', InputOption::VALUE_OPTIONAL, 'Path to Config file')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $configPath = $input->getOption('config');

        if ($configPath !== null && !is_string($configPath)) {
            throw new RuntimeException('Invalid config option given');
        }

        $prLink = $input->getArgument('pr');

        if (!is_string($prLink)) {
            throw new RuntimeException('The PR links needs to be a string');
        }

        $context = $this->assembleContextByUrl($prLink);
        $config = $this->configLoader->loadByPath($configPath);

        $this->runner->run($config, $context);

        return $this->handleReport($input, $output, $context);
    }

    private function assembleContextByUrl(string $url): Context
    {
        $pregMatch = preg_match('/^https:\/\/github\.com\/(?<owner>[\w\-_]*)\/(?<repo>[\w\-_]*)\/pull\/(?<id>\d*)/', $url, $matches);

        if ($pregMatch === 0) {
            throw new InvalidArgumentException('The given url must be a valid Github URL');
        }

        $this->github->load($matches['owner'] . '/' . $matches['repo'], $matches['id']);

        return new Context($this->github);
    }
}
