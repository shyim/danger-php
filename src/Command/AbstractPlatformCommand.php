<?php declare(strict_types=1);

namespace Danger\Command;

use Danger\Context;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class AbstractPlatformCommand extends Command
{
    protected function handleReport(InputInterface $input, OutputInterface $output, Context $context): int
    {
        $io = new SymfonyStyle($input, $output);

        if (!$context->hasReports()) {
            $io->success('PR looks good!');

            return 0;
        }

        $failed = false;

        if ($context->hasFailures()) {
            $io->table(['Failures'], array_map(static fn (string $msg) => [$msg], $context->getFailures()));
            $failed = true;
        }

        if ($context->hasWarnings()) {
            $io->table(['Warnings'], array_map(static fn (string $msg) => [$msg], $context->getWarnings()));
        }

        if ($context->hasNotices()) {
            $io->table(['Notices'], array_map(static fn (string $msg) => [$msg], $context->getNotices()));
        }

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
