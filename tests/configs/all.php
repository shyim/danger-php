<?php declare(strict_types=1);

use Danger\Config;

return
    (new Config())
        ->useRule(function (Danger\Context $context): void {
            $context->failure('A Failure');
            $context->warning('A Warning');
            $context->notice('A Notice');
        })
;
