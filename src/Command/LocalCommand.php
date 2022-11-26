<?php declare(strict_types=1);

namespace Danger\Command;

use Danger\ConfigLoader;
use Danger\Context;
use Danger\Platform\Local\LocalPlatform;
use Danger\Runner;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

#[AsCommand(name: 'local')]
class LocalCommand extends AbstractPlatformCommand
{
    public function __construct(private ConfigLoader $configLoader, private Runner $runner, private LocalPlatform $localPlatform)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Run local danger on local git')
            ->addOption('root', null, InputOption::VALUE_OPTIONAL, 'Git Path', (string) getcwd())
            ->addOption('head-branch', null, InputOption::VALUE_OPTIONAL, 'Head Branch')
            ->addOption('config', 'c', InputOption::VALUE_OPTIONAL, 'Path to Config file')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $configPath = $input->getOption('config');
        $headBranch = $input->getOption('head-branch');
        $root = $input->getOption('root');

        if ($configPath !== null && !\is_string($configPath)) {
            throw new \InvalidArgumentException('Invalid config option given');
        }

        if (!\is_string($root)) {
            throw new \InvalidArgumentException('Invalid root option given');
        }

        if ($headBranch === null) {
            $process = new Process(['git', 'symbolic-ref', '--short', 'refs/remotes/origin/HEAD']);
            $process->mustRun();
            $headBranch = trim(basename($process->getOutput()));
        }

        $process = new Process(['git', 'rev-parse', '--abbrev-ref', 'HEAD']);
        $process->mustRun();
        $localBranch = trim(basename($process->getOutput()));

        $config = $this->configLoader->loadByPath($configPath);

        $this->localPlatform->load($root, $localBranch . '|' . $headBranch);

        $context = new Context($this->localPlatform);

        $this->runner->run($config, $context);

        return $this->handleReport($input, $output, $context);
    }
}
