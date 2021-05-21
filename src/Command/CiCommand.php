<?php
declare(strict_types=1);

namespace Danger\Command;

use Danger\Component\ConfigLoader;
use Danger\Component\Platform\PlatformDetector;
use Danger\Component\Renderer\HTMLRenderer;
use Danger\Component\Runner;
use Danger\Context;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CiCommand extends Command
{
    public static $defaultName = 'ci';

    public function __construct(
        private PlatformDetector $platformDetector,
        private ConfigLoader $configLoader,
        private Runner $runner,
        private HTMLRenderer $renderer
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Runs danger on CI')
            ->addOption('config', 'c', InputOption::VALUE_OPTIONAL, 'Path to Config file')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $context = new Context($this->platformDetector->detect());

        $config = $this->configLoader->loadByPath($input->getOption('config'));

        $this->runner->run($config, $context);
        $io = new SymfonyStyle($input, $output);

        if (!$context->hasReports()) {
            $context->platform->removePost($config);

            $io->success('Looks good!');

            return 0;
        }

        $body = $this->renderer->convert($context);

        $commentLink = $context->platform->post($body, $config);

        $io->info('The comment has been created at ' . $commentLink);

        return -1;
    }
}
