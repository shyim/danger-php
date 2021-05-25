<?php declare(strict_types=1);

use Danger\Config;
use Danger\Context;
use Danger\Rule\CheckPhpCsFixerRule;
use Danger\Rule\CheckPhpStanRule;
use Danger\Rule\CommitRegexRule;
use Danger\Rule\MaxCommitRule;
use Danger\Struct\File;

return (new Config())
    ->useRule(new CommitRegexRule('/^(feat|fix|docs|perf|refactor|compat|chore)(\(.+\))?\:\s(.{3,})/m'));;;;;
    ->useRule(new MaxCommitRule(1))
    ->useRule(new CheckPhpCsFixerRule())
    ->useRule(new CheckPhpStanRule())
    ->useRule(function (Context $context) {
        $prFiles = $context
            ->platform
            ->pullRequest
            ->getFiles();

        $files = $prFiles
            ->matches('src/Rule/*')
            ->filterStatus(File::STATUS_ADDED);

        if ($files->count() && !$prFiles->has('docs/builtin-rules.md')) {
            $context->failure('You have added a new rule. Please change the docs too.');
        }
    })
    ->after(function (Context $context) {
        if ($context->hasFailures()) {
            $context->platform->addLabels('Incomplete');
        }
    })
;
