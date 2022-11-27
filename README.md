# Danger PHP

Danger runs during your CI process, and gives teams the chance to automate common code review chores.
This project ports [Danger](https://danger.systems/ruby/) to PHP.

Currently only GitHub and Gitlab are supported as Platform


## Badges

[![MIT License](https://img.shields.io/apm/l/atomic-design-ui.svg?)](https://github.com/shyim/danger-php/blob/main/LICENSE)
[![codecov](https://codecov.io/gh/shyim/danger-php/branch/main/graph/badge.svg)](https://codecov.io/gh/shyim/danger-php)


## Installation

### Composer

Install danger-php using Composer

```bash 
composer global require shyim/danger-php
```

### Phar Archive

Every release has a phar archive attached

### Docker

Use the [prebuilt Docker image](https://github.com/users/shyim/packages/container/package/danger-php)

## Documentation

- [Getting started](./docs/getting_started.md)
- [Builtin Rules](./docs/builtin-rules.md)
- [Danger Context](./docs/context.md)
- [CI Integration](./docs/ci.md)
- [Commands](./docs/commands.md)

### Disallow multiple commits with same message

```php
<?php declare(strict_types=1);

use Danger\Config;
use Danger\Rule\DisallowRepeatedCommits;

return (new Config())
    ->useRule(new DisallowRepeatedCommits) // Disallows multiple commits with the same message
;
```

### Only allow one commit in Pull Request

```php
<?php declare(strict_types=1);

use Danger\Config;
use Danger\Rule\MaxCommit;

return (new Config())
    ->useRule(new MaxCommit(1))
;


```

### Check for modification on CHANGELOG.md

```php
<?php declare(strict_types=1);

use Danger\Config;
use Danger\Context;

return (new Config())
    ->useRule(function (Context $context): void {
        if (!$context->platform->pullRequest->getFiles()->has('CHANGELOG.md')) {
            $context->failure('Please edit also the CHANGELOG.md');
        }
    })
;

```

### Check for Assignee in PR

```php
<?php declare(strict_types=1);

use Danger\Config;
use Danger\Context;

return (new Config())
    ->useRule(function (Context $context): void {
        if (count($context->platform->pullRequest->assignees) === 0) {
            $context->warning('This PR currently doesn\'t have an assignee');
        }
    })
;

```

## Screenshots

![Example Comment](https://i.imgur.com/e2OEChE.png)


## License

[MIT](https://choosealicense.com/licenses/mit/)

  
