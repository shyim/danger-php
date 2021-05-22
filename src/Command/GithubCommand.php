<?php
declare(strict_types=1);

namespace Danger\Command;

use Danger\ConfigLoader;
use Danger\Context;
use Danger\Platform\Github\Github;
use Danger\Runner;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class GithubCommand extends Command
{
    public static $defaultName = 'github-local';

    public function __construct(private Github $github, private ConfigLoader $configLoader, private Runner $runner)
    {
        parent::__construct();
    }

    public function configure(): void
    {
        $this
            ->setDescription('Run local danger against an Github PR without Commenting')
            ->addArgument('pr', InputArgument::REQUIRED, 'Github PR URL')
            ->addOption('config', 'c', InputOption::VALUE_OPTIONAL, 'Path to Config file')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $context = $this->assembleContextByUrl($input->getArgument('pr'));
        $config = $this->configLoader->loadByPath($input->getOption('config'));

        $this->runner->run($config, $context);

        $io = new SymfonyStyle($input, $output);

        if (!$context->hasReports()) {
            $io->success('PR looks good!');

            return 0;
        }

        $failed = false;

        if ($context->hasFailures()) {
            $io->table(['Failures'], array_map(fn (string $msg) => [$msg], $context->getFailures()));
            $failed = true;
        }

        if ($context->hasWarnings()) {
            $io->table(['Warnings'], array_map(fn (string $msg) => [$msg], $context->getWarnings()));
        }

        if ($context->hasNotices()) {
            $io->table(['Notices'], array_map(fn (string $msg) => [$msg], $context->getNotices()));
        }

        return $failed ? -1 : 0;
    }

    private function assembleContextByUrl(string $url): Context
    {
        if (!preg_match('/^https:\/\/github\.com\/(?<owner>[\w\-_]*)\/(?<repo>[\w\-_]*)\/pull\/(?<id>\d*)/', $url, $matches)) {
            throw new \InvalidArgumentException('The given url must be a valid Github URL');
        }

        $this->github->load($matches['owner'] . '/' . $matches['repo'], $matches['id']);

        return new Context($this->github);
    }
}
