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