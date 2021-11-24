<?php
declare(strict_types=1);

namespace Danger\Rule;

use Danger\Context;

class CheckPhpStan
{
    public function __construct(
        private string $command = './vendor/bin/phpstan --error-format=json --no-progress 2> /dev/null',
        private string $message = 'PHPStan check failed. Run locally <code>./vendor/bin/phpstan --error-format=json --no-progress</code> to see the errors.'
    ) {
    }

    public function __invoke(Context $context): void
    {
        exec($this->command, $cmdOutput, $resultCode);

        if ($resultCode !== 0) {
            $context->failure($this->message);
        }
    }
}
