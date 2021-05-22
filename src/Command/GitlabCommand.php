<?php
declare(strict_types=1);

namespace Danger\Command;

use Danger\ConfigLoader;
use Danger\Context;
use Danger\Platform\Gitlab\Gitlab;
use Danger\Runner;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class GitlabCommand extends Command
{
    public static $defaultName = 'gitlab-local';

    public function __construct(private Gitlab $gitlab, private ConfigLoader $configLoader, private Runner $runner)
    {
        parent::__construct();
    }

    public function configure(): void
    {
        $this
            ->setDescription('Run local danger against an Gitlab PR without Commenting')
            ->addArgument('projectIdentifier', InputArgument::REQUIRED, 'Gitlab Project ID')
            ->addArgument('mrID', InputArgument::REQUIRED, 'Gitlab Merge Request ID')
            ->addOption('config', 'c', InputOption::VALUE_OPTIONAL, 'Path to Config file')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (!isset($_SERVER['DANGER_GITLAB_TOKEN'])) {
            $io->error('You need the environment variable DANGER_GITLAB_TOKEN with an Gitlab API Token to use this command');

            return -1;
        }

        $this->gitlab->load($input->getArgument('projectIdentifier'), $input->getArgument('mrID'));

        $context = new Context($this->gitlab);
        $config = $this->configLoader->loadByPath($input->getOption('config'));

        $this->runner->run($config, $context);

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
}
