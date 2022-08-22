<?php
declare(strict_types=1);

namespace Danger\Command;

use function assert;

use Danger\ConfigLoader;
use Danger\Context;
use Danger\Platform\Gitlab\Gitlab;
use Danger\Runner;
use InvalidArgumentException;

use function is_string;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class GitlabCommand extends AbstractPlatformCommand
{
    public static $defaultName = 'gitlab-local';

    public function __construct(private Gitlab $gitlab, private ConfigLoader $configLoader, private Runner $runner)
    {
        parent::__construct();
    }

    protected function configure(): void
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

            return self::FAILURE;
        }

        $projectIdentifier = $input->getArgument('projectIdentifier');
        $mrID = $input->getArgument('mrID');

        assert(is_string($projectIdentifier));
        assert(is_string($mrID));

        $configPath = $input->getOption('config');

        if ($configPath !== null && !is_string($configPath)) {
            throw new InvalidArgumentException('Invalid config option given');
        }

        $this->gitlab->load($projectIdentifier, $mrID);

        $context = new Context($this->gitlab);
        $config = $this->configLoader->loadByPath($configPath);

        $this->runner->run($config, $context);

        return $this->handleReport($input, $output, $context);
    }
}
