<?php declare(strict_types=1);

use Danger\Config;

return
    (new Config())
        ->useRule(function (Danger\Context $context): void {
            $context->warning('Test');
        })
    ;
