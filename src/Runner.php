<?php
declare(strict_types=1);

namespace Danger;

class Runner
{
    public function run(Config $config, Context $context): void
    {
        foreach($config->getRules() as $rule) {
            $rule($context);
        }

        /**
         * Run after hooks. Can be used to set labels after all rules has been run
         */
        foreach ($config->getAfterHooks() as $afterHook) {
            $afterHook($context);
        }

        if ($config->getReportLevel($context) < $config->getUseThreadOn()) {
            $config->useThreadOn(Config::REPORT_LEVEL_NONE);
        }
    }
}
