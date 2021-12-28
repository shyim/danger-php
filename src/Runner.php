<?php
declare(strict_types=1);

namespace Danger;

use function count;

class Runner
{
    public function run(Config $config, Context $context): void
    {
        foreach ($config->getRules() as $rule) {
            $rule($context);
        }

        /**
         * Run after hooks. Can be used to set labels after all rules has been run
         */
        foreach ($config->getAfterHooks() as $afterHook) {
            $afterHook($context);
        }

        /**
         * When useThreadOnFails is enabled but no failures enabled deactivate it
         */
        if ($config->isThreadEnabled() && count($context->getFailures()) === 0) {
            $config->useThreadOnFails(false);
        }
    }
}
