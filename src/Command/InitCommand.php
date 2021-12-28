<?php
declare(strict_types=1);

namespace Danger\Command;

use const LOCK_EX;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class InitCommand extends Command
{
    public static $defaultName = 'init';

    protected function configure(): void
    {
        $this->setDescription('Initializes a new danger.php');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $path = getcwd() . '/.danger.php';

        $io = new SymfonyStyle($input, $output);

        if (is_file($path) && !$io->confirm('A .danger.php file does already exist. Do you want to override it?')) {
            return 0;
        }

        file_put_contents($path, '<?php declare(strict_types=1);

use Danger\Config;
use Danger\Rule\DisallowRepeatedCommits;

return (new Config())
    ->useRule(new DisallowRepeatedCommits) // Disallows multiple commits with the same message
;
', LOCK_EX);
        $io->success(sprintf('Created %s', $path));

        return 0;
    }
}
