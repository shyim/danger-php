<?php
declare(strict_types=1);

namespace Danger\Component;

use Danger\Config;
use Danger\Context;

class Runner
{
    public function run(Config $config, Context $context): void
    {
        foreach ($config->getRules() as $rule) {
            $rule($context);
        }

        /**
         * When useThreadOnFails is enabled but no failures enabled deactivate it
         */
        if ($config->isThreadEnabled() && count($context->getFailures()) === 0) {
            $config->useThreadOnFails(false);
        }
    }
}
