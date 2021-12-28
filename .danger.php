<?php declare(strict_types=1);

use Danger\Config;
use Danger\Context;
use Danger\Rule\CheckPhpCsFixer;
use Danger\Rule\CheckPhpStan;
use Danger\Rule\CommitRegex;
use Danger\Struct\File;

return (new Config())
    ->useRule(new CommitRegex('/^(feat|fix|docs|perf|refactor|compat|chore)(\(.+\))?\:\s(.{3,})/m'))
    ->useRule(new CheckPhpCsFixer())
    ->useRule(new CheckPhpStan())
    ->useRule(static function (Context $context): void {
        $prFiles = $context
            ->platform
            ->pullRequest
            ->getFiles()
        ;

        $files = $prFiles
            ->matches('src/Rule/*')
            ->filterStatus(File::STATUS_ADDED)
        ;

        if ($files->count() && !$prFiles->has('docs/builtin-rules.md')) {
            $context->failure('You have added a new rule. Please change the docs too.');
        }
    })
    ->after(static function (Context $context): void {
        if ($context->hasFailures()) {
            $context->platform->addLabels('Incomplete');

            return;
        }

        $context->platform->removeLabels('Incomplete');
    })
;
