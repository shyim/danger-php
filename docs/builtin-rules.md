# Builtin rules

## \Danger\Rule\CheckPhpCsFixer

Runs PHP-CS-Fixer in background and adds a failure if the command is failed

## \Danger\Rule\CommitRegex

Checks that the Commit message matches the regex

### Parameters

- regex (string)
- (optional) message (string)

## \Danger\Rule\DisallowRepeatedCommits

Checks that the commit messages are unique inside the pull request

### Parameters

- (optional) message (string)

## \Danger\Rule\MaxCommit

Checks the commit amount in the pull request

### Parameters

- maxAmount (int) default: 1
- (optional) message (string)

## \Danger\Rule\Condition

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
use Danger\Rule\CommitRegex;
use Danger\Rule\Condition;
use Danger\Rule\MaxCommit;

/**
 * We check the commit amount and commit message only when the target platform is Github
 */
return (new Config())
    ->useRule(new Condition(
            function (Context $context) {
                return $context->platform instanceof Github;
            },
            [
                new MaxCommit(1),
                new CommitRegex('/^(feat|fix|docs|perf|refactor|compat|chore)(\(.+\))?\:\s(.{3,})/m')
            ]
        ));
```