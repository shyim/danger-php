# Builtin rules

## \Danger\Rule\CheckPhpCsFixerRule

Runs PHP-CS-Fixer in background and adds a failure if the command is failed

## \Danger\Rule\CommitRegexRule

Checks that the Commit message matches the regex

### Parameters

- regex (string)
- (optional) message (string)

## \Danger\Rule\DisallowRepeatedCommitsRule

Checks that the commit messages are unique inside the pull request

### Parameters

- (optional) message (string)

## \Danger\Rule\MaxCommitRule

Checks the commit amount in the pull request

### Parameters

- maxAmount (int) default: 1
- (optional) message (string)

## \Danger\Rule\ConditionRule

Allows running multiple rules when a condition is met

### Parameters

- function which checks are the condition met
- array of rules to be executed


### Example

```php
<?php

use Danger\Context;
use Danger\Config;
use Danger\Platform\Github\Github;
use Danger\Rule\CommitRegexRule;
use Danger\Rule\ConditionRule;
use Danger\Rule\MaxCommitRule;

/**
 * We check the commit amount and commit message only when the target platform is Github
 */
return (new Config())
    ->useRule(new ConditionRule(
            function (Context $context) {
                return $context->platform instanceof Github;
            },
            [
                new MaxCommitRule(1),
                new CommitRegexRule('/^(feat|fix|docs|perf|refactor|compat|chore)(\(.+\))?\:\s(.{3,})/m')
            ]
        ));
```